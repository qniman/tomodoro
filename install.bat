@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ===============================================
echo   Полная установка приложения Tomodoro
echo ===============================================
echo.

REM Проверка прав администратора
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Требуются права администратора!
    echo Пожалуйста, запустите скрипт от имени администратора
    pause
    exit /b 1
)

REM Проверка Chocolatey
echo Проверка Chocolatey...
choco --version >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ===============================================
    echo   Установка Chocolatey
    echo ===============================================
    echo Chocolatey не найден. Установка...
    echo.
    @"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -InputFormat None -ExecutionPolicy Bypass -Command "[System.Net.ServicePointManager]::SecurityProtocol = 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"
    if %errorlevel% neq 0 (
        echo ❌ Ошибка при установке Chocolatey
        pause
        exit /b 1
    )
    echo ✓ Chocolatey установлен
)
echo ✓ Chocolatey найден

REM Проверка и установка PHP
echo.
echo ===============================================
echo   Проверка и установка PHP
echo ===============================================
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo PHP не найден. Установка PHP 8.2...
    choco install php --version=8.2.0 -y
    if %errorlevel% neq 0 (
        echo ❌ Ошибка при установке PHP
        pause
        exit /b 1
    )
    echo ✓ PHP установлен
    REM Обновляем PATH
    set "PATH=%PATH%;C:\tools\php82"
) else (
    echo ✓ PHP уже установлен
    php -v | findstr /R ".*"
)

REM Проверка и установка Node.js
echo.
echo ===============================================
echo   Проверка и установка Node.js
echo ===============================================
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo Node.js не найден. Установка Node.js LTS...
    choco install nodejs --version=20.10.0 -y
    if %errorlevel% neq 0 (
        echo ❌ Ошибка при установке Node.js
        pause
        exit /b 1
    )
    echo ✓ Node.js установлен
    REM Обновляем PATH
    set "PATH=%PATH%;C:\Program Files\nodejs"
) else (
    echo ✓ Node.js уже установлен
    node -v
)

REM Проверка NPM
npm -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ NPM не найден
    pause
    exit /b 1
)

REM Проверка и установка Composer
echo.
echo ===============================================
echo   Проверка и установка Composer
echo ===============================================
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Composer не найден. Установка Composer...
    choco install composer -y
    if %errorlevel% neq 0 (
        echo ❌ Ошибка при установке Composer
        pause
        exit /b 1
    )
    echo ✓ Composer установлен
    REM Обновляем PATH
    set "PATH=%PATH%;C:\ProgramData\ComposerSetup\bin"
) else (
    echo ✓ Composer уже установлен
    composer --version | findstr /R ".*"
)

REM Проверка и установка Git
echo.
echo ===============================================
echo   Проверка и установка Git
echo ===============================================
git --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Git не найден. Установка Git...
    choco install git -y
    if %errorlevel% neq 0 (
        echo ❌ Ошибка при установке Git
        pause
        exit /b 1
    )
    echo ✓ Git установлен
    set "PATH=%PATH%;C:\Program Files\Git\cmd"
) else (
    echo ✓ Git уже установлен
    git --version | findstr /R ".*"
)

echo.
echo ===============================================
echo   Установка зависимостей PHP (Composer)
echo ===============================================
echo.
composer install
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
    if exist ".env.example" (
        echo Создание файла .env из .env.example...
        copy .env.example .env
    ) else (
        echo Создание файла .env с настройками по умолчанию...
        (
            echo APP_NAME=Tomodoro
            echo APP_ENV=local
            echo APP_DEBUG=true
            echo APP_URL=http://localhost:8000
            echo APP_TIMEZONE=Europe/Moscow
            echo.
            echo DB_CONNECTION=sqlite
            echo DB_HOST=127.0.0.1
            echo DB_PORT=3306
            echo DB_DATABASE=tomodoro
            echo DB_USERNAME=root
            echo DB_PASSWORD=
            echo.
            echo MAIL_MAILER=smtp
            echo MAIL_HOST=smtp.mailtrap.io
            echo MAIL_PORT=587
            echo MAIL_USERNAME=
            echo MAIL_PASSWORD=
            echo MAIL_ENCRYPTION=tls
            echo MAIL_FROM_ADDRESS=admin@tomodoro.local
        ) > .env
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

REM Создание БД
echo.
echo ===============================================
echo   Подготовка базы данных
echo ===============================================
echo.
if not exist "database\database.sqlite" (
    echo Создание файла БД...
    type nul > database\database.sqlite
)

REM Миграции БД
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

REM Сборка фронтенда
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
