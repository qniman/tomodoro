#!/usr/bin/env bash
#
# Деплой Tomodoro после git pull: Composer, Vite, Artisan, storage:link.
#
# Использование (из любой директории):
#   chmod +x scripts/deploy.sh
#   ./scripts/deploy.sh
#   ./scripts/deploy.sh --branch production
#   ./scripts/deploy.sh --no-git           # не делать git pull
#   ./scripts/deploy.sh --no-npm           # пропустить npm (например собираете локально)
#   ./scripts/deploy.sh --no-migrate       # без миграций
#   ./scripts/deploy.sh --dev              # composer с dev-зависимостями (не для prod)
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_ROOT"

BRANCH="main"
REMOTE="origin"
SKIP_GIT=0
SKIP_NPM=0
SKIP_MIGRATE=0
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
        --no-git)   SKIP_GIT=1; shift ;;
        --no-npm)   SKIP_NPM=1; shift ;;
        --no-migrate) SKIP_MIGRATE=1; shift ;;
        --dev)      COMPOSER_DEV=""; shift ;;
        -h|--help)
            cat << 'EOF'
deploy.sh — деплой после git pull

  ./scripts/deploy.sh
  ./scripts/deploy.sh --branch main
  ./scripts/deploy.sh --no-git
  ./scripts/deploy.sh --no-npm
  ./scripts/deploy.sh --no-migrate
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

if [[ "$SKIP_GIT" -eq 0 ]]; then
    echo "==> git pull $REMOTE $BRANCH"
    git fetch "$REMOTE"
    git pull "$REMOTE" "$BRANCH"
else
    echo "==> git pull пропущен (--no-git)"
fi

if [[ -n "$COMPOSER_DEV" ]]; then
    echo "==> composer install $COMPOSER_DEV"
    composer install --no-dev --optimize-autoloader
else
    echo "==> composer install (с dev)"
    composer install --optimize-autoloader
fi

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

if [[ "$SKIP_MIGRATE" -eq 0 ]]; then
    echo "==> php artisan migrate --force"
    php artisan migrate --force
else
    echo "==> миграции пропущены (--no-migrate)"
fi

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

if php artisan queue:work --help >/dev/null 2>&1; then
    echo "==> php artisan queue:restart (если настроены очереди)"
    php artisan queue:restart 2>/dev/null || true
fi

echo ""
echo "Готово. Проверьте .env (APP_URL, APP_DEBUG=false на prod). Жёсткое обновление в браузере: Ctrl+F5."
