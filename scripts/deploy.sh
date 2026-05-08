#!/usr/bin/env bash
#
# Деплой Tomodoro после git pull: Composer, Vite, Artisan, Reverb.
#
# Использование (из любой директории):
#   chmod +x scripts/deploy.sh
#   ./scripts/deploy.sh
#   ./scripts/deploy.sh --branch production
#   ./scripts/deploy.sh --no-git           # не делать git pull
#   ./scripts/deploy.sh --no-npm           # пропустить npm (например собираете локально)
#   ./scripts/deploy.sh --no-migrate       # без миграций
#   ./scripts/deploy.sh --no-reverb        # не перезапускать Reverb
#   ./scripts/deploy.sh --dev              # composer с dev-зависимостями (не для prod)
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

# Ветка для pull: origin/HEAD (main или master у разных проектов), иначе master.
BRANCH="$(git symbolic-ref refs/remotes/origin/HEAD 2>/dev/null | sed 's@^refs/remotes/origin/@@')"
if [[ -z "$BRANCH" ]]; then
    for cand in master main; do
        git show-ref --verify --quiet "refs/remotes/origin/$cand" 2>/dev/null || continue
        BRANCH="$cand"
        break
    done
fi
BRANCH="${BRANCH:-master}"
REMOTE="origin"
SKIP_GIT=0
SKIP_NPM=0
SKIP_MIGRATE=0
SKIP_REVERB=0
COMPOSER_DEV="--no-dev --optimize-autoloader"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --branch)
            BRANCH="${2:?}"
            shift 2
            ;;
        --remote)
            REMOTE="${2:?}"
            shift 2
            ;;
        --no-git)     SKIP_GIT=1;     shift ;;
        --no-npm)     SKIP_NPM=1;     shift ;;
        --no-migrate) SKIP_MIGRATE=1; shift ;;
        --no-reverb)  SKIP_REVERB=1;  shift ;;
        --dev)        COMPOSER_DEV=""; shift ;;
        -h|--help)
            cat << 'EOF'
deploy.sh — деплой после git pull

  ./scripts/deploy.sh
  ./scripts/deploy.sh --branch master    # по умолчанию: origin/HEAD (у нас master)
  ./scripts/deploy.sh --no-git
  ./scripts/deploy.sh --no-npm
  ./scripts/deploy.sh --no-migrate
  ./scripts/deploy.sh --no-reverb        # не перезапускать Laravel Reverb
  ./scripts/deploy.sh --dev
EOF
            exit 0
            ;;
        *)
            echo "Неизвестный аргумент: $1" >&2
            exit 1
            ;;
    esac
done

echo "==> Корень проекта: $PROJECT_ROOT"

# ── 1. Git ────────────────────────────────────────────────────────────────────
if [[ "$SKIP_GIT" -eq 0 ]]; then
    echo "==> git pull $REMOTE $BRANCH"
    git fetch "$REMOTE"
    git pull "$REMOTE" "$BRANCH"
else
    echo "==> git pull пропущен (--no-git)"
fi

# ── 2. Composer ───────────────────────────────────────────────────────────────
if [[ -n "$COMPOSER_DEV" ]]; then
    echo "==> composer install --no-dev --optimize-autoloader"
    composer install --no-dev --optimize-autoloader
else
    echo "==> composer install (с dev)"
    composer install --optimize-autoloader
fi

# ── 3. npm / Vite ────────────────────────────────────────────────────────────
if [[ "$SKIP_NPM" -eq 0 ]]; then
    echo "==> npm ci && npm run build"
    if ! command -v node >/dev/null 2>&1; then
        echo "Ошибка: node не найден. Установите Node >= 20 или запустите с --no-npm и выложите собранный public/build/ отдельно." >&2
        exit 1
    fi
    npm ci
    npm run build
else
    echo "==> npm пропущен (--no-npm); убедитесь что public/build/ актуален"
fi

# ── 4. Миграции ───────────────────────────────────────────────────────────────
if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
    echo "==> php artisan migrate --force"
    php artisan migrate --force
else
    echo "==> миграции пропущены (--no-migrate)"
fi

# ── 5. Системные команды Laravel ─────────────────────────────────────────────
echo "==> php artisan storage:link (без ошибки если уже есть)"
php artisan storage:link 2>/dev/null || true

echo "==> php artisan config:cache"
php artisan config:cache

echo "==> php artisan route:cache"
php artisan route:cache

echo "==> php artisan view:cache"
php artisan view:cache

echo "==> php artisan optimize"
php artisan optimize

# ── 6. Очереди ───────────────────────────────────────────────────────────────
if php artisan queue:work --help >/dev/null 2>&1; then
    echo "==> php artisan queue:restart (если настроены очереди)"
    php artisan queue:restart 2>/dev/null || true
fi

# ── 7. Laravel Reverb (WebSocket-сервер для Workspace) ───────────────────────
if [[ "$SKIP_REVERB" -eq 0 ]]; then
    echo "==> Перезапуск Laravel Reverb..."

    REVERB_RESTARTED=0

    # Supervisor: ищем процесс с «reverb» в имени группы
    if command -v supervisorctl >/dev/null 2>&1; then
        if supervisorctl status 2>/dev/null | grep -qi "reverb"; then
            echo "    supervisorctl restart reverb:*"
            supervisorctl restart reverb:* 2>/dev/null && REVERB_RESTARTED=1 || true
        fi
    fi

    # systemd: ищем юнит tomodoro-reverb.service или laravel-reverb.service
    if [[ "$REVERB_RESTARTED" -eq 0 ]] && command -v systemctl >/dev/null 2>&1; then
        for unit in tomodoro-reverb laravel-reverb reverb; do
            if systemctl is-active --quiet "${unit}.service" 2>/dev/null; then
                echo "    systemctl restart ${unit}.service"
                systemctl restart "${unit}.service" && REVERB_RESTARTED=1 && break || true
            fi
        done
    fi

    # Fallback: убиваем артизан-процессы reverb:start и сообщаем
    if [[ "$REVERB_RESTARTED" -eq 0 ]]; then
        if pkill -f "artisan reverb:start" 2>/dev/null; then
            echo "    Старый процесс reverb:start остановлен."
            echo "    Запустите вручную: php artisan reverb:start --host=0.0.0.0 --port=\${REVERB_PORT:-8080} &"
        else
            echo "    Reverb не запущен или не управляется supervisor/systemd."
            echo "    Для запуска: php artisan reverb:start --host=0.0.0.0 --port=\${REVERB_PORT:-8080}"
        fi
        echo ""
        echo "    Совет: добавьте в supervisor конфиг (пример ниже) для автоперезапуска:"
        cat << 'SUPERVISOR_HINT'
    [program:reverb]
    command=php /var/www/tomodoro/artisan reverb:start --host=0.0.0.0 --port=8080
    directory=/var/www/tomodoro
    autostart=true
    autorestart=true
    redirect_stderr=true
    stdout_logfile=/var/log/reverb.log
SUPERVISOR_HINT
    fi
else
    echo "==> Reverb пропущен (--no-reverb)"
fi

# ── Итог ──────────────────────────────────────────────────────────────────────
echo ""
echo "✓ Деплой завершён."
echo ""
echo "Проверьте .env:"
echo "  APP_URL              — публичный URL приложения"
echo "  APP_DEBUG=false      — на продакшене"
echo "  BROADCAST_CONNECTION=reverb"
echo "  REVERB_APP_ID / REVERB_APP_KEY / REVERB_APP_SECRET"
echo "  REVERB_HOST          — публичный хост (или IP) для WebSocket"
echo "  REVERB_PORT          — порт (по умолчанию 8080)"
echo "  VITE_REVERB_*        — должны совпадать с REVERB_* (нужны для пересборки фронта)"
echo ""
echo "Жёсткое обновление в браузере: Ctrl+F5."
