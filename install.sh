#!/bin/bash

set -e  # Прерывать при любой ошибке

echo ""
echo "███████████████████████████████████████████"
echo "  TOMODORO - Автоматическая установка"
echo "███████████████████████████████████████████"
echo ""

# Цвета
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Функции
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

print_status() {
    echo -e "${BLUE}[*]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
    exit 1
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

# Trap для ошибок
trap 'print_error "Ошибка установки на шаге: $BASH_COMMAND"' ERR

# Определение ОС
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_TYPE=$ID
    fi
elif [[ "$OSTYPE" == "darwin"* ]]; then
    OS_TYPE="macos"
fi

# ============================================
# Установка для Ubuntu/Debian
# ============================================
install_debian() {
    print_status "Обновление репозиториев..."
    sudo apt-get update -qq || print_error "Ошибка обновления репозиториев"

    # Проверяем версию PHP
    PHP_VERSION=$(php -v 2>/dev/null | grep -oP 'PHP \K[0-9]+\.[0-9]+' | head -1)
    
    if ! command_exists php; then
        print_status "Установка PHP 8.3..."
        sudo apt-get install -y php8.3-cli php8.3-fpm php8.3-sqlite3 php8.3-curl php8.3-xml php8.3-dom php8.3-mbstring php8.3-zip php8.3-intl php8.3-dev 2>&1 | tail -5 || print_error "Ошибка установки PHP"
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен (версия: $PHP_VERSION)"
        # Убедимся что установлены нужные расширения
        if ! php -m | grep -q xml; then
            print_status "Установка недостающих PHP расширений..."
            sudo apt-get install -y php${PHP_VERSION}-xml php${PHP_VERSION}-dom 2>&1 | tail -5 || print_error "Ошибка установки PHP расширений"
            print_success "PHP расширения установлены"
        fi
    fi

    if ! command_exists node; then
        print_status "Установка Node.js 20 LTS..."
        curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1 || print_error "Ошибка добавления репозитория Node.js"
        sudo apt-get install -y nodejs >/dev/null 2>&1 || print_error "Ошибка установки Node.js"
        print_success "Node.js установлен"
    else
        NODE_VERSION=$(node -v)
        print_success "Node.js уже установлен ($NODE_VERSION)"
        # Проверяем версию
        NODE_MAJOR=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
        if [ "$NODE_MAJOR" -lt 20 ]; then
            print_warning "Node.js версия $NODE_MAJOR < 20. Обновляем..."
            curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1 || print_error "Ошибка добавления репозитория Node.js"
            sudo apt-get install -y nodejs >/dev/null 2>&1 || print_error "Ошибка обновления Node.js"
            print_success "Node.js обновлен на версию $(node -v)"
        fi
    fi

    if ! command_exists npm; then
        print_status "Установка NPM..."
        sudo apt-get install -y npm >/dev/null 2>&1 || print_error "Ошибка установки NPM"
        print_success "NPM установлен"
    else
        print_success "NPM уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        curl -sS https://getcomposer.org/installer | php >/dev/null 2>&1 || print_error "Ошибка загрузки Composer"
        sudo mv composer.phar /usr/local/bin/composer >/dev/null 2>&1 || print_error "Ошибка перемещения Composer"
        sudo chmod +x /usr/local/bin/composer >/dev/null 2>&1
        print_success "Composer установлен"
    else
        print_success "Composer уже установлен"
    fi
}

# ============================================
# Установка для CentOS/RHEL
# ============================================
install_redhat() {
    print_status "Обновление пакетов..."
    sudo yum update -y -q >/dev/null 2>&1 || print_error "Ошибка обновления пакетов"

    if ! command_exists php; then
        print_status "Установка PHP 8.2..."
        sudo yum install -y php php-cli php-fpm php-sqlite php-curl php-xml php-mbstring php-zip php-intl php-devel >/dev/null 2>&1 || print_error "Ошибка установки PHP"
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен"
    fi

    if ! command_exists node; then
        print_status "Установка Node.js..."
        curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash - >/dev/null 2>&1 || print_error "Ошибка добавления репозитория Node.js"
        sudo yum install -y nodejs >/dev/null 2>&1 || print_error "Ошибка установки Node.js"
        print_success "Node.js установлен"
    else
        print_success "Node.js уже установлен"
    fi

    if ! command_exists npm; then
        print_status "Установка NPM..."
        sudo yum install -y npm >/dev/null 2>&1 || print_error "Ошибка установки NPM"
        print_success "NPM установлен"
    else
        print_success "NPM уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        curl -sS https://getcomposer.org/installer | php >/dev/null 2>&1 || print_error "Ошибка загрузки Composer"
        sudo mv composer.phar /usr/local/bin/composer >/dev/null 2>&1 || print_error "Ошибка перемещения Composer"
        sudo chmod +x /usr/local/bin/composer >/dev/null 2>&1
        print_success "Composer установлен"
    else
        print_success "Composer уже установлен"
    fi
}

# ============================================
# Установка для macOS
# ============================================
install_macos() {
    if ! command_exists brew; then
        print_status "Установка Homebrew..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" >/dev/null 2>&1 || print_error "Ошибка установки Homebrew"
    fi

    if ! command_exists php; then
        print_status "Установка PHP..."
        brew install php >/dev/null 2>&1 || print_error "Ошибка установки PHP"
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен"
    fi

    if ! command_exists node; then
        print_status "Установка Node.js..."
        brew install node >/dev/null 2>&1 || print_error "Ошибка установки Node.js"
        print_success "Node.js установлен"
    else
        print_success "Node.js уже установлен"
    fi

    if ! command_exists npm; then
        print_status "Установка NPM..."
        brew install npm >/dev/null 2>&1 || print_error "Ошибка установки NPM"
        print_success "NPM установлен"
    else
        print_success "NPM уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        brew install composer >/dev/null 2>&1 || print_error "Ошибка установки Composer"
        print_success "Composer установлен"
    else
        print_success "Composer уже установлен"
    fi
}

# ============================================
# Основная установка
# ============================================

# Определение и установка необходимых инструментов
echo ""
if [[ "$OS_TYPE" == "ubuntu" ]] || [[ "$OS_TYPE" == "debian" ]]; then
    print_status "Обнаружена система на базе Debian"
    install_debian
elif [[ "$OS_TYPE" == "centos" ]] || [[ "$OS_TYPE" == "rhel" ]] || [[ "$OS_TYPE" == "fedora" ]]; then
    print_status "Обнаружена система на базе RedHat"
    install_redhat
elif [[ "$OS_TYPE" == "macos" ]]; then
    print_status "Обнаружена система macOS"
    install_macos
else
    print_warning "Неизвестная ОС. Попытка использовать Ubuntu команды..."
    install_debian
fi

# Проверка обязательных инструментов
echo ""
print_status "Финальная проверка..."

if ! command_exists php; then
    print_error "PHP не установлен"
fi
print_success "PHP готов"

# Проверяем требуемые расширения
REQUIRED_EXTENSIONS=("xml" "dom" "curl" "mbstring" "zip" "sqlite3")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "^$ext$"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    print_warning "Отсутствуют PHP расширения: ${MISSING_EXTENSIONS[*]}"
    print_status "Установка недостающих расширений..."
    PHP_VERSION=$(php -v | grep -oP 'PHP \K[0-9]+\.[0-9]+' | head -1)
    sudo apt-get update -qq
    for ext in "${MISSING_EXTENSIONS[@]}"; do
        print_status "  Установка php${PHP_VERSION}-$ext..."
        sudo apt-get install -y php${PHP_VERSION}-${ext} >/dev/null 2>&1 || print_warning "Не удалось установить php-$ext"
    done
    print_success "Расширения установлены"
fi

if ! command_exists node; then
    print_error "Node.js не установлен"
fi
NODE_VERSION=$(node -v)
NODE_MAJOR=$(echo $NODE_VERSION | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_MAJOR" -lt 20 ]; then
    print_warning "Node.js версия $NODE_VERSION < 20. Обновляем для совместимости..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1
    sudo apt-get install -y nodejs >/dev/null 2>&1 || print_error "Ошибка обновления Node.js"
    NODE_VERSION=$(node -v)
fi
print_success "Node.js готов ($NODE_VERSION)"

if ! command_exists composer; then
    print_error "Composer не установлен"
fi
print_success "Composer готов"

if ! command_exists npm; then
    print_error "NPM не установлен"
fi
print_success "NPM готов"

# ============================================
# Подготовка Laravel директорий
# ============================================
echo ""
print_status "Подготовка директорий приложения..."

# Создаем требуемые директории для Laravel
mkdir -p bootstrap/cache
mkdir -p storage/app
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p database

# Убедимся что директории имеют правильные права
chmod -R 755 bootstrap storage database 2>/dev/null || true

print_success "Директории подготовлены"

# ============================================
# Установка зависимостей
# ============================================
echo ""
print_status "Установка PHP зависимостей..."
composer install --no-interaction 2>&1 | tee /tmp/composer.log
COMPOSER_EXIT=${PIPESTATUS[0]}

if [ $COMPOSER_EXIT -ne 0 ]; then
    echo ""
    print_error "Ошибка установки PHP зависимостей."
    echo ""
    echo "Проверьте:"
    echo "  1. PHP расширения: php -m | grep -E 'xml|dom|curl|mbstring|zip'"
    echo "  2. Права доступа: ls -la bootstrap/cache storage/"
    echo "  3. Память: free -h"
    echo "  4. Место на диске: df -h"
    echo ""
    echo "Последние ошибки:"
    tail -30 /tmp/composer.log
    exit 1
fi
print_success "PHP зависимости установлены"

echo ""
print_status "Установка Node.js зависимостей..."
npm install 2>&1 | tee /tmp/npm.log
NPM_EXIT=${PIPESTATUS[0]}

if [ $NPM_EXIT -ne 0 ]; then
    echo ""
    print_error "Ошибка установки Node.js зависимостей."
    echo "Лог ошибки:"
    tail -20 /tmp/npm.log
    exit 1
fi
print_success "Node.js зависимости установлены"

# ============================================
# Конфигурация
# ============================================
echo ""
print_status "Настройка приложения..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        cat > .env << 'EOF'
APP_NAME=Tomodoro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/Moscow

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOF
    fi
fi

print_success "Конфиг готов"

# ============================================
# Инициализация приложения
# ============================================
# Инициализация приложения
# ============================================
echo ""
print_status "Финальная подготовка..."

# Убедимся что все директории существуют и имеют правильные права
mkdir -p bootstrap/cache storage/app storage/logs storage/framework/{cache,sessions,views} database

# Давим на права (т.к. уже создали выше, но убедимся еще раз)
chmod -R 755 bootstrap storage database 2>/dev/null || true

# Создаем SQLite БД если её нет
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    chmod 644 database/database.sqlite
fi

print_status "Генерация ключа приложения..."
php artisan key:generate --force 2>&1 | tee /tmp/artisan-key.log
KEY_EXIT=$?
if [ $KEY_EXIT -ne 0 ]; then
    print_error "Ошибка генерации ключа"
    tail -20 /tmp/artisan-key.log
    exit 1
fi

print_status "Запуск миграций..."
php artisan migrate --force --no-interaction 2>&1 | tee /tmp/artisan-migrate.log
MIGRATE_EXIT=$?
if [ $MIGRATE_EXIT -ne 0 ]; then
    print_error "Ошибка выполнения миграций"
    tail -20 /tmp/artisan-migrate.log
    exit 1
fi

print_status "Сборка фронтенда..."
npm run build 2>&1 | tee /tmp/npm-build.log
BUILD_EXIT=${PIPESTATUS[0]}

if [ $BUILD_EXIT -ne 0 ]; then
    echo ""
    print_error "Ошибка сборки фронтенда"
    echo "Лог ошибки:"
    tail -30 /tmp/npm-build.log
    exit 1
fi
print_success "Фронтенд собран"

# ============================================
# Готово
# ============================================
echo ""
echo "███████████████████████████████████████████"
echo "  ✓ Установка завершена!"
echo "███████████████████████████████████████████"
echo ""
print_status "Запуск приложения..."
echo ""
echo "🌐 Откройте в браузере: http://localhost:8000"
echo ""

php artisan serve
