#!/bin/bash

echo "========================================"
echo "  Установка и запуск приложения Tomodoro"
echo "========================================"
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для проверки команды
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Проверка PHP
echo "Проверка PHP..."
if ! command_exists php; then
    echo -e "${RED}❌ PHP не установлен!${NC}"
    echo "Пожалуйста, установите PHP"
    exit 1
fi
echo -e "${GREEN}✓ PHP найден$(php -v | head -n 1)${NC}"

# Проверка Composer
echo "Проверка Composer..."
if ! command_exists composer; then
    echo -e "${RED}❌ Composer не установлен!${NC}"
    echo "Пожалуйста, установите Composer"
    exit 1
fi
echo -e "${GREEN}✓ Composer найден${NC}"

# Проверка Node.js
echo "Проверка Node.js..."
if ! command_exists node; then
    echo -e "${RED}❌ Node.js не установлен!${NC}"
    echo "Пожалуйста, установите Node.js"
    exit 1
fi
echo -e "${GREEN}✓ Node.js найден$(node -v)${NC}"

# Проверка NPM
echo "Проверка NPM..."
if ! command_exists npm; then
    echo -e "${RED}❌ NPM не установлен!${NC}"
    echo "Пожалуйста, установите NPM"
    exit 1
fi
echo -e "${GREEN}✓ NPM найден$(npm -v)${NC}"

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
        echo -e "${YELLOW}⚠ Внимание: файл .env.example не найден${NC}"
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
