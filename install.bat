@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion
setlocal enableextensions

color 0A
cls

echo.
echo ===================================================
echo   TOMODORO - Automated Installation
echo ===================================================
echo.

REM Admin check
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Running as administrator...
    powershell -Command "Start-Process cmd.exe -ArgumentList '/c %~s0' -Verb RunAs"
    exit /b 1
)

setlocal
cd /d "%~dp0"

set CHOCO_INSTALLED=0
set NEEDS_PATH_UPDATE=0

REM ============================================
REM Chocolatey installation
REM ============================================
echo [*] Checking Chocolatey...
choco --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Installing Chocolatey...
    @"%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe" -NoProfile -InputFormat None -ExecutionPolicy Bypass -Command "[System.Net.ServicePointManager]::SecurityProtocol = 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"
    if %errorlevel% neq 0 (
        echo [X] Chocolatey installation error
        pause
        exit /b 1
    )
    set CHOCO_INSTALLED=1
) else (
    echo [OK] Chocolatey found
)

set "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"

REM ============================================
REM PHP installation
REM ============================================
echo.
echo [*] Checking PHP...
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Installing PHP 8.2...
    choco install php --version=8.2.13 -y -f >nul 2>&1
    if %errorlevel% neq 0 (
        echo [X] PHP installation error
        pause
        exit /b 1
    )
    set NEEDS_PATH_UPDATE=1
    set "PATH=%PATH%;C:\tools\php82"
    echo [OK] PHP installed
) else (
    echo [OK] PHP already installed
)

REM ============================================
REM Node.js installation
REM ============================================
echo.
echo [*] Checking Node.js...
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Installing Node.js...
    choco install nodejs --version=20.10.0 -y -f >nul 2>&1
    if %errorlevel% neq 0 (
        echo [X] Node.js installation error
        pause
        exit /b 1
    )
    set NEEDS_PATH_UPDATE=1
    set "PATH=%PATH%;C:\Program Files\nodejs"
    echo [OK] Node.js installed
) else (
    echo [OK] Node.js already installed
)

REM ============================================
REM Composer installation
REM ============================================
echo.
echo [*] Checking Composer...
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [!] Installing Composer...
    choco install composer -y -f >nul 2>&1
    if %errorlevel% neq 0 (
        echo [X] Composer installation error
        pause
        exit /b 1
    )
    set NEEDS_PATH_UPDATE=1
    set "PATH=%PATH%;C:\ProgramData\ComposerSetup\bin"
    echo [OK] Composer installed
) else (
    echo [OK] Composer already installed
)

REM ============================================
REM Final checks
REM ============================================
echo.
echo [*] Final verification...
npm -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] NPM not found
    pause
    exit /b 1
)
echo [OK] NPM ready

php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] PHP not found
    pause
    exit /b 1
)
echo [OK] PHP ready

composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Composer not found
    pause
    exit /b 1
)
echo [OK] Composer ready

node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Node.js not found
    pause
    exit /b 1
)
echo [OK] Node.js ready

REM ============================================
REM Environment update
REM ============================================
if %NEEDS_PATH_UPDATE% equ 1 (
    echo.
    echo [!] Updating environment...
    call refreshenv.cmd >nul 2>&1
)

REM ============================================
REM Dependencies installation
REM ============================================
echo.
echo [*] Installing PHP dependencies...
call composer install --no-interaction 2>&1
if %errorlevel% neq 0 (
    echo [X] PHP dependencies installation error
    pause
    exit /b 1
)
echo [OK] PHP dependencies installed

echo.
echo [*] Installing Node.js dependencies...
call npm install 2>&1
if %errorlevel% neq 0 (
    echo [X] Node.js dependencies installation error
    pause
    exit /b 1
)
echo [OK] Node.js dependencies installed

REM ============================================
REM Application configuration
REM ============================================
echo.
echo [*] Configuring application...

if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
    ) else (
        (
            echo APP_NAME=Tomodoro
            echo APP_ENV=local
            echo APP_DEBUG=true
            echo APP_URL=http://localhost:8000
            echo APP_TIMEZONE=UTC
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

echo [OK] Configuration ready

REM ============================================
REM Application initialization
REM ============================================
echo.
echo [*] Generating app key...
php artisan key:generate --force >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] App key generation error
    pause
    exit /b 1
)

echo [*] Preparing database...
if not exist "database" mkdir database
if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
)

echo [*] Running migrations...
php artisan migrate --force --no-interaction >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Migration execution error
    pause
    exit /b 1
)

echo [*] Building frontend...
call npm run build >nul 2>&1
if %errorlevel% neq 0 (
    echo [X] Frontend build error
    pause
    exit /b 1
)

REM ============================================
REM Ready
REM ============================================
echo.
echo ===================================================
echo   [OK] Installation completed!
echo ===================================================
echo.
echo [*] Starting application...
echo.
echo [>] Open in browser: http://localhost:8000
echo.

php artisan serve

pause

