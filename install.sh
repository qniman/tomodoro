#!/bin/bash

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
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

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
    sudo apt-get update -qq

    if ! command_exists php; then
        print_status "Установка PHP 8.2..."
        sudo apt-get install -y -qq php8.2-cli php8.2-fpm php8.2-sqlite3 php8.2-curl php8.2-xml php8.2-mbstring php8.2-zip php8.2-intl >/dev/null 2>&1
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен"
    fi

    if ! command_exists node; then
        print_status "Установка Node.js..."
        curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1
        sudo apt-get install -y -qq nodejs >/dev/null 2>&1
        print_success "Node.js установлен"
    else
        print_success "Node.js уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        curl -sS https://getcomposer.org/installer | php >/dev/null 2>&1
        sudo mv composer.phar /usr/local/bin/composer >/dev/null 2>&1
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
    sudo yum update -y -q >/dev/null 2>&1

    if ! command_exists php; then
        print_status "Установка PHP 8.2..."
        sudo yum install -y -q php php-cli php-fpm php-sqlite php-curl php-xml php-mbstring php-zip php-intl >/dev/null 2>&1
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен"
    fi

    if ! command_exists node; then
        print_status "Установка Node.js..."
        curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash - >/dev/null 2>&1
        sudo yum install -y -q nodejs >/dev/null 2>&1
        print_success "Node.js установлен"
    else
        print_success "Node.js уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        curl -sS https://getcomposer.org/installer | php >/dev/null 2>&1
        sudo mv composer.phar /usr/local/bin/composer >/dev/null 2>&1
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
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" >/dev/null 2>&1
    fi

    if ! command_exists php; then
        print_status "Установка PHP..."
        brew install php >/dev/null 2>&1
        print_success "PHP установлен"
    else
        print_success "PHP уже установлен"
    fi

    if ! command_exists node; then
        print_status "Установка Node.js..."
        brew install node >/dev/null 2>&1
        print_success "Node.js установлен"
    else
        print_success "Node.js уже установлен"
    fi

    if ! command_exists composer; then
        print_status "Установка Composer..."
        brew install composer >/dev/null 2>&1
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
    exit 1
fi

if ! command_exists node; then
    print_error "Node.js не установлен"
    exit 1
fi

if ! command_exists composer; then
    print_error "Composer не установлен"
    exit 1
fi

if ! command_exists npm; then
    print_error "NPM не установлен"
    exit 1
fi

print_success "Все необходимые инструменты готовы"

# ============================================
# Установка зависимостей
# ============================================
echo ""
print_status "Установка PHP зависимостей..."
composer install --no-interaction -q
print_success "PHP зависимости установлены"

echo ""
print_status "Установка Node.js зависимостей..."
npm install --silent
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
echo ""
print_status "Генерация ключа приложения..."
php artisan key:generate --force >/dev/null 2>&1

print_status "Подготовка БД..."
mkdir -p database
touch database/database.sqlite 2>/dev/null

print_status "Запуск миграций..."
php artisan migrate --force --no-interaction >/dev/null 2>&1

print_status "Сборка фронтенда..."
npm run build >/dev/null 2>&1

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
