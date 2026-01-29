@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

color 0A
cls

echo.
echo ███████████████████████████████████████████
echo   TOMODORO - Автоматическая установка
echo ███████████████████████████████████████████
echo.

REM Проверка прав администратора
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Запуск от имени администратора...
    powershell -Command "Start-Process cmd.exe -ArgumentList '/c %~s0' -Verb RunAs"
    exit /b 1
)

setlocal
cd /d "%~dp0"

REM Переменные для путей установки
set CHOCO_INSTALLED=0
set NEEDS_PATH_UPDATE=0

REM ============================================
REM Установка Chocolatey
REM ============================================
echo [*] Проверка Chocolatey...
choco --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Установка Chocolatey...
    @"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -InputFormat None -ExecutionPolicy Bypass -Command "[System.Net.ServicePointManager]::SecurityProtocol = 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"
    set CHOCO_INSTALLED=1
) else (
    echo [✓] Chocolatey уже установлен
)

REM Обновляем PATH
set "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"

REM ============================================
REM Установка PHP
REM ============================================
echo.
echo [*] Проверка PHP...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Установка PHP 8.2...
    choco install php --version=8.2.13 -y -f
    set NEEDS_PATH_UPDATE=1
    REM Обновляем PATH для PHP
    for /f "delims=" %%A in ('choco list php --local ^| find "php"') do (
        setx PATH "%PATH%;C:\tools\php82"
    )
    set "PATH=%PATH%;C:\tools\php82"
) else (
    echo [✓] PHP найден
    php -v | findstr /R ".*" | findstr /v "^$"
)

REM ============================================
REM Установка Node.js
REM ============================================
echo.
echo [*] Проверка Node.js...
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Установка Node.js...
    choco install nodejs --version=20.10.0 -y -f
    set NEEDS_PATH_UPDATE=1
    set "PATH=%PATH%;C:\Program Files\nodejs"
) else (
    echo [✓] Node.js найден
    node -v
)

REM ============================================
REM Установка Composer
REM ============================================
echo.
echo [*] Проверка Composer...
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Установка Composer...
    choco install composer -y -f
    set NEEDS_PATH_UPDATE=1
    set "PATH=%PATH%;C:\ProgramData\ComposerSetup\bin"
) else (
    echo [✓] Composer найден
    composer --version | findstr /R ".*"
)

REM ============================================
REM Проверка NPM
REM ============================================
echo.
echo [*] Проверка NPM...
npm -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [✗] NPM не найден
    exit /b 1
)
echo [✓] NPM найден

REM ============================================
REM Обновление переменных окружения если нужно
REM ============================================
if %NEEDS_PATH_UPDATE% equ 1 (
    echo.
    echo [!] Обновление переменных окружения...
    call refreshenv.cmd
)

REM ============================================
REM Установка зависимостей
REM ============================================
echo.
echo [*] Установка зависимостей PHP...
call composer install --no-interaction
if %errorlevel% neq 0 (
    echo [✗] Ошибка установки PHP зависимостей
    exit /b 1
)
echo [✓] PHP зависимости установлены

echo.
echo [*] Установка зависимостей Node.js...
call npm install
if %errorlevel% neq 0 (
    echo [✗] Ошибка установки Node.js зависимостей
    exit /b 1
)
echo [✓] Node.js зависимости установлены

REM ============================================
REM Конфигурация приложения
REM ============================================
echo.
echo [*] Настройка приложения...

if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
    ) else (
        (
            echo APP_NAME=Tomodoro
            echo APP_ENV=local
            echo APP_DEBUG=true
            echo APP_URL=http://localhost:8000
            echo APP_TIMEZONE=Europe/Moscow
            echo.
            echo DB_CONNECTION=sqlite
            echo DB_DATABASE=database/database.sqlite
            echo.
            echo CACHE_DRIVER=file
            echo SESSION_DRIVER=file
            echo QUEUE_CONNECTION=sync
        ) > .env
    )
)

echo [✓] Конфиг приложения готов

REM ============================================
REM Инициализация приложения
REM ============================================
echo.
echo [*] Генерация ключа приложения...
php artisan key:generate --force >nul 2>&1

echo [*] Подготовка БД...
if not exist "database" mkdir database
if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
)

echo [*] Запуск миграций...
php artisan migrate --force --no-interaction >nul 2>&1

echo [*] Сборка фронтенда...
npm run build >nul 2>&1

REM ============================================
REM Готово
REM ============================================
echo.
echo ███████████████████████████████████████████
echo   ✓ Установка завершена!
echo ███████████████████████████████████████████
echo.
echo [►] Запуск приложения...
echo.
echo 🌐 Откройте в браузере: http://localhost:8000
echo.

php artisan serve

