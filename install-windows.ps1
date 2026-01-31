#Requires -RunAsAdministrator
# Tomodoro One-Liner Install - Windows PowerShell
# Usage: powershell -ExecutionPolicy Bypass -File install-windows.ps1

$ErrorActionPreference = "Stop"
$WarningPreference = "SilentlyContinue"

Write-Host ""
Write-Host "🎯 Tomodoro Setup - Windows" -ForegroundColor Cyan
Write-Host "=============================" -ForegroundColor Cyan
Write-Host ""

# Function to check command
function Test-CommandExists {
    param($command)
    $null = Get-Command $command -ErrorAction SilentlyContinue
    return $?
}

# Install Chocolatey if needed
if (-not (Test-CommandExists choco)) {
    Write-Host "📦 Installing Chocolatey..." -ForegroundColor Yellow
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
}

# Install dependencies
Write-Host "📦 Installing dependencies (PHP, Node.js, Composer)..." -ForegroundColor Yellow
if (-not (Test-CommandExists php)) {
    choco install php -y | Out-Null
}
if (-not (Test-CommandExists node)) {
    choco install nodejs -y | Out-Null
}
if (-not (Test-CommandExists composer)) {
    choco install composer -y | Out-Null
}

# Refresh PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

# Verify installations
Write-Host "✅ Verifying installations..." -ForegroundColor Green
php -v | Select-Object -First 1 | Write-Host
node -v | Write-Host
composer --version | Write-Host

# Install project dependencies
Write-Host "📦 Installing project dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader
npm install

# Setup environment
Write-Host "⚙️ Configuring environment..." -ForegroundColor Yellow
if (-not (Test-Path .env)) {
    if (Test-Path .env.example) {
        Copy-Item .env.example .env
    } else {
        "APP_KEY=base64:$((-join ((0..9)+(65..90)+(97..122)|Get-Random -Count 44 | % {[char]$_})))" | Out-File .env
    }
}

Write-Host "🔑 Generating app key..." -ForegroundColor Yellow
php artisan key:generate --force

Write-Host "🗄️ Setting up database..." -ForegroundColor Yellow
php artisan migrate --seed --force

Write-Host "🎨 Building frontend..." -ForegroundColor Yellow
npm run build

Write-Host ""
Write-Host "✅ Installation complete!" -ForegroundColor Green
Write-Host "🚀 Starting development server..." -ForegroundColor Cyan
Write-Host "📱 Open http://localhost:8000" -ForegroundColor Cyan
Write-Host ""

php artisan serve

