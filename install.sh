#!/bin/bash

echo "========================================"
echo "  Полная установка приложения Tomodoro"
echo "========================================"
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция для проверки команды
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Определение ОС
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    OS="linux"
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        DISTRO=$ID
    fi
elif [[ "$OSTYPE" == "darwin"* ]]; then
    OS="macos"
else
    OS="unknown"
fi

echo -e "${BLUE}Определена ОС: $OS${NC}"

# Функция установки на Ubuntu/Debian
install_ubuntu() {
    echo -e "${BLUE}Обновление репозиториев...${NC}"
    sudo apt-get update

    echo ""
    echo "========================================"
    echo "  Установка PHP"
    echo "========================================"
    if ! command_exists php; then
        echo -e "${BLUE}Установка PHP 8.2 и расширений...${NC}"
        sudo apt-get install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-sqlite3 php8.2-curl php8.2-xml php8.2-mbstring php8.2-zip php8.2-intl
        echo -e "${GREEN}✓ PHP установлен${NC}"
    else
        echo -e "${GREEN}✓ PHP уже установлен$(php -v | head -n 1)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Node.js и NPM"
    echo "========================================"
    if ! command_exists node; then
        echo -e "${BLUE}Установка Node.js...${NC}"
        curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
        sudo apt-get install -y nodejs
        echo -e "${GREEN}✓ Node.js установлен$(node -v)${NC}"
    else
        echo -e "${GREEN}✓ Node.js уже установлен$(node -v)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Composer"
    echo "========================================"
    if ! command_exists composer; then
        echo -e "${BLUE}Установка Composer...${NC}"
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
        echo -e "${GREEN}✓ Composer установлен${NC}"
    else
        echo -e "${GREEN}✓ Composer уже установлен${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Git"
    echo "========================================"
    if ! command_exists git; then
        echo -e "${BLUE}Установка Git...${NC}"
        sudo apt-get install -y git
        echo -e "${GREEN}✓ Git установлен${NC}"
    else
        echo -e "${GREEN}✓ Git уже установлен$(git --version)${NC}"
    fi
}

# Функция установки на CentOS/RHEL
install_centos() {
    echo -e "${BLUE}Обновление пакетов...${NC}"
    sudo yum update -y

    echo ""
    echo "========================================"
    echo "  Установка PHP"
    echo "========================================"
    if ! command_exists php; then
        echo -e "${BLUE}Установка PHP 8.2 и расширений...${NC}"
        sudo yum install -y php php-cli php-fpm php-mysql php-sqlite php-curl php-xml php-mbstring php-zip php-intl
        echo -e "${GREEN}✓ PHP установлен${NC}"
    else
        echo -e "${GREEN}✓ PHP уже установлен$(php -v | head -n 1)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Node.js и NPM"
    echo "========================================"
    if ! command_exists node; then
        echo -e "${BLUE}Установка Node.js...${NC}"
        curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
        sudo yum install -y nodejs
        echo -e "${GREEN}✓ Node.js установлен$(node -v)${NC}"
    else
        echo -e "${GREEN}✓ Node.js уже установлен$(node -v)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Composer"
    echo "========================================"
    if ! command_exists composer; then
        echo -e "${BLUE}Установка Composer...${NC}"
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
        sudo chmod +x /usr/local/bin/composer
        echo -e "${GREEN}✓ Composer установлен${NC}"
    else
        echo -e "${GREEN}✓ Composer уже установлен${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Git"
    echo "========================================"
    if ! command_exists git; then
        echo -e "${BLUE}Установка Git...${NC}"
        sudo yum install -y git
        echo -e "${GREEN}✓ Git установлен${NC}"
    else
        echo -e "${GREEN}✓ Git уже установлен$(git --version)${NC}"
    fi
}

# Функция установки на macOS
install_macos() {
    echo -e "${BLUE}Проверка Homebrew...${NC}"
    if ! command_exists brew; then
        echo -e "${BLUE}Установка Homebrew...${NC}"
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    fi

    echo ""
    echo "========================================"
    echo "  Установка PHP"
    echo "========================================"
    if ! command_exists php; then
        echo -e "${BLUE}Установка PHP...${NC}"
        brew install php
        echo -e "${GREEN}✓ PHP установлен$(php -v | head -n 1)${NC}"
    else
        echo -e "${GREEN}✓ PHP уже установлен$(php -v | head -n 1)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Node.js и NPM"
    echo "========================================"
    if ! command_exists node; then
        echo -e "${BLUE}Установка Node.js...${NC}"
        brew install node
        echo -e "${GREEN}✓ Node.js установлен$(node -v)${NC}"
    else
        echo -e "${GREEN}✓ Node.js уже установлен$(node -v)${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Composer"
    echo "========================================"
    if ! command_exists composer; then
        echo -e "${BLUE}Установка Composer...${NC}"
        brew install composer
        echo -e "${GREEN}✓ Composer установлен${NC}"
    else
        echo -e "${GREEN}✓ Composer уже установлен${NC}"
    fi

    echo ""
    echo "========================================"
    echo "  Установка Git"
    echo "========================================"
    if ! command_exists git; then
        echo -e "${BLUE}Установка Git...${NC}"
        brew install git
        echo -e "${GREEN}✓ Git установлен${NC}"
    else
        echo -e "${GREEN}✓ Git уже установлен$(git --version)${NC}"
    fi
}

# Выбор функции установки в зависимости от ОС
if [ "$OS" == "linux" ]; then
    if [[ "$DISTRO" == "ubuntu" ]] || [[ "$DISTRO" == "debian" ]]; then
        install_ubuntu
    elif [[ "$DISTRO" == "centos" ]] || [[ "$DISTRO" == "rhel" ]] || [[ "$DISTRO" == "fedora" ]]; then
        install_centos
    else
        echo -e "${YELLOW}⚠ Неизвестный дистрибутив Linux. Попытка использовать Ubuntu команды...${NC}"
        install_ubuntu
    fi
elif [ "$OS" == "macos" ]; then
    install_macos
else
    echo -e "${RED}❌ Неподдерживаемая ОС${NC}"
    exit 1
fi

echo ""
echo "========================================"
echo "  Установка зависимостей PHP (Composer)"
echo "========================================"
echo ""
composer install
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при установке зависимостей PHP!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Зависимости PHP установлены${NC}"

echo ""
echo "========================================"
echo "  Установка зависимостей Node.js (NPM)"
echo "========================================"
echo ""
npm install
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Ошибка при установке зависимостей Node.js!${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Зависимости Node.js установлены${NC}"

# Настройка .env
echo ""
echo "========================================"
echo "  Настройка переменных окружения"
echo "========================================"
echo ""
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "Создан файл .env из .env.example"
    else
        echo "Создание файла .env с настройками по умолчанию..."
        cat > .env << 'EOF'
APP_NAME=Tomodoro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Europe/Moscow

DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tomodoro
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=admin@tomodoro.local
EOF
    fi
fi
echo -e "${GREEN}✓ Файл .env готов${NC}"

# Генерация ключа приложения
echo ""
echo "========================================"
echo "  Генерация ключа приложения"
echo "========================================"
echo ""
php artisan key:generate
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ Внимание: ошибка при генерации ключа${NC}"
fi

# Подготовка БД
echo ""
echo "========================================"
echo "  Подготовка базы данных"
echo "========================================"
echo ""
mkdir -p database
if [ ! -f "database/database.sqlite" ]; then
    echo "Создание файла БД..."
    touch database/database.sqlite
fi

# Миграции БД
echo ""
echo "========================================"
echo "  Запуск миграций БД"
echo "========================================"
echo ""
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ Внимание: ошибка при выполнении миграций${NC}"
    echo "Проверьте настройки БД в файле .env"
fi

# Сборка фронтенда
echo ""
echo "========================================"
echo "  Сборка фронтенда"
echo "========================================"
echo ""
npm run build
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ Внимание: ошибка при сборке фронтенда${NC}"
fi

echo ""
echo "========================================"
echo "  Установка завершена!"
echo "========================================"
echo ""
echo "Запуск приложения..."
echo ""
echo "Откройте в браузере: http://localhost:8000"
echo ""
echo "Для остановки сервера нажмите Ctrl+C"
echo ""

# Запуск сервера
php artisan serve
