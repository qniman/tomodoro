@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ===============================================
echo   Установка и запуск приложения Tomodoro
echo ===============================================
echo.

REM Проверка наличия PHP
echo Проверка PHP...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP не установлен или недоступен!
    echo Пожалуйста, установите PHP и добавьте его в PATH
    pause
    exit /b 1
)
echo ✓ PHP найден

REM Проверка наличия Composer
echo Проверка Composer...
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer не установлен или недоступен!
    echo Пожалуйста, установите Composer
    pause
    exit /b 1
)
echo ✓ Composer найден

REM Проверка наличия Node.js
echo Проверка Node.js...
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Node.js не установлен или недоступен!
    echo Пожалуйста, установите Node.js
    pause
    exit /b 1
)
echo ✓ Node.js найден

echo.
echo ===============================================
echo   Установка зависимостей PHP (Composer)
echo ===============================================
echo.
call composer install
if %errorlevel% neq 0 (
    echo ❌ Ошибка при установке зависимостей PHP!
    pause
    exit /b 1
)
echo ✓ Зависимости PHP установлены

echo.
echo ===============================================
echo   Установка зависимостей Node.js (NPM)
echo ===============================================
echo.
call npm install
if %errorlevel% neq 0 (
    echo ❌ Ошибка при установке зависимостей Node.js!
    pause
    exit /b 1
)
echo ✓ Зависимости Node.js установлены

REM Проверка файла .env
echo.
echo ===============================================
echo   Настройка переменных окружения
echo ===============================================
echo.
if not exist ".env" (
    echo Создание файла .env из .env.example...
    copy .env.example .env
    if %errorlevel% neq 0 (
        echo ⚠ Внимание: файл .env.example не найден
    )
)
echo ✓ Файл .env готов

REM Генерация ключа приложения
echo.
echo ===============================================
echo   Генерация ключа приложения
echo ===============================================
echo.
call php artisan key:generate
if %errorlevel% neq 0 (
    echo ⚠ Внимание: ошибка при генерации ключа
)

echo.
echo ===============================================
echo   Запуск миграций БД
echo ===============================================
echo.
call php artisan migrate --force
if %errorlevel% neq 0 (
    echo ⚠ Внимание: ошибка при выполнении миграций
    echo Проверьте настройки БД в файле .env
)

echo.
echo ===============================================
echo   Сборка фронтенда
echo ===============================================
echo.
call npm run build
if %errorlevel% neq 0 (
    echo ⚠ Внимание: ошибка при сборке фронтенда
)

echo.
echo ===============================================
echo   Установка завершена!
echo ===============================================
echo.
echo Запуск приложения...
echo.
echo Откройте в браузере: http://localhost:8000
echo.
echo Для остановки сервера нажмите Ctrl+C
echo.

REM Запуск сервера
call php artisan serve

pause
