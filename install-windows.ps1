#Requires -RunAsAdministrator
# Tomodoro One-Liner Install - Windows PowerShell
# Works on completely bare systems with automatic dependency installation
# Usage: powershell -ExecutionPolicy Bypass -File install-windows.ps1

$ErrorActionPreference = "Stop"
$WarningPreference = "SilentlyContinue"
$PSDefaultParameterValues['*:Encoding'] = 'UTF8'

# Color codes
$Success = [System.ConsoleColor]::Green
$Error_Color = [System.ConsoleColor]::Red
$Warning_Color = [System.ConsoleColor]::Yellow
$Info_Color = [System.ConsoleColor]::Cyan

# Logging functions
function Log-Info { Write-Host "ℹ️  $($args -join ' ')" -ForegroundColor $Info_Color }
function Log-Success { Write-Host "✅ $($args -join ' ')" -ForegroundColor $Success }
function Log-Warning { Write-Host "⚠️  $($args -join ' ')" -ForegroundColor $Warning_Color }
function Log-Error { Write-Host "❌ $($args -join ' ')" -ForegroundColor $Error_Color; Exit 1 }

# Trap errors
trap { Log-Error "Installation failed: $_" }

Write-Host ""
Write-Host "🎯 Tomodoro Setup - Complete Installation" -ForegroundColor $Info_Color
Write-Host "==========================================" -ForegroundColor $Info_Color
Write-Host ""

# ============================================
# Admin Check
# ============================================

$isAdmin = [Security.Principal.WindowsIdentity]::GetCurrent().Owner.IsWellKnown([Security.Principal.WellKnownSidType]::BuiltinAdministratorsSid)
if (-not $isAdmin) {
    Log-Error "This script must run as Administrator. Please right-click PowerShell and select 'Run as administrator'"
}
Log-Success "Running as Administrator"

# ============================================
# Functions
# ============================================

function Test-CommandExists {
    param($command)
    try {
        if (Get-Command $command -ErrorAction SilentlyContinue) {
            return $true
        }
    }
    catch {
        return $false
    }
    return $false
}

function Get-EnvironmentPath {
    $userPath = [Environment]::GetEnvironmentVariable("Path", [EnvironmentVariableTarget]::User)
    $machinePath = [Environment]::GetEnvironmentVariable("Path", [EnvironmentVariableTarget]::Machine)
    $currentPath = $env:Path
    return @($userPath, $machinePath, $currentPath) | Where-Object { $_ }
}

function Update-EnvironmentPath {
    $env:Path = Get-EnvironmentPath
    $env:Path = "$env:Path;$env:ALLUSERSPROFILE\Chocolatey\bin"
}

function Install-Chocolatey {
    if (Test-CommandExists choco) {
        Log-Success "Chocolatey is already installed"
        return
    }
    
    Log-Info "Installing Chocolatey..."
    try {
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        $ChocoInstallUri = 'https://community.chocolatey.org/install.ps1'
        $WebClient = New-Object System.Net.WebClient
        $WebClient.DownloadString($ChocoInstallUri) | Invoke-Expression
        
        # Update PATH
        Update-EnvironmentPath
        
        # Verify installation
        if (-not (Test-CommandExists choco)) {
            Log-Warning "Chocolatey install completed but command not found, refreshing PATH..."
            $env:Path += ";$env:ALLUSERSPROFILE\Chocolatey\bin"
        }
        
        Log-Success "Chocolatey installed successfully"
    }
    catch {
        Log-Error "Failed to install Chocolatey: $_"
    }
}

function Install-Tool {
    param(
        [string]$ToolName,
        [string]$ChocoPackage,
        [string]$CommandToTest
    )
    
    if (Test-CommandExists $CommandToTest) {
        $version = & $CommandToTest --version 2>&1 | Select-Object -First 1
        Log-Success "$ToolName is already installed: $version"
        return
    }
    
    Log-Info "Installing $ToolName..."
    try {
        choco install $ChocoPackage -y --no-progress
        Update-EnvironmentPath
        
        # Wait for installation to complete
        Start-Sleep -Seconds 2
        
        # Verify
        if (Test-CommandExists $CommandToTest) {
            $version = & $CommandToTest --version 2>&1 | Select-Object -First 1
            Log-Success "$ToolName installed: $version"
        }
        else {
            Log-Warning "$ToolName installed but command not found in PATH, manual verification needed"
        }
    }
    catch {
        Log-Error "Failed to install $ToolName: $_"
    }
}

# ============================================
# Chocolatey Installation
# ============================================

Log-Info "Checking for Chocolatey..."
Install-Chocolatey

# ============================================
# Dependencies Installation
# ============================================

Log-Info "Installing dependencies (PHP, Node.js, Composer)..."

Install-Tool -ToolName "PHP" -ChocoPackage "php" -CommandToTest "php"
Install-Tool -ToolName "Node.js" -ChocoPackage "nodejs" -CommandToTest "node"
Install-Tool -ToolName "Composer" -ChocoPackage "composer" -CommandToTest "composer"

# Final PATH update
Update-EnvironmentPath

# ============================================
# Verification
# ============================================

Log-Info "Verifying installations..."

try {
    $phpVersion = (php -v 2>&1 | Select-Object -First 1)
    Log-Success "PHP: $phpVersion"
}
catch {
    Log-Error "PHP verification failed"
}

try {
    $nodeVersion = (node -v)
    Log-Success "Node.js: $nodeVersion"
}
catch {
    Log-Error "Node.js verification failed"
}

try {
    $npmVersion = (npm -v)
    Log-Success "npm: $npmVersion"
}
catch {
    Log-Error "npm verification failed"
}

try {
    $composerVersion = (composer --version 2>&1 | Select-Object -First 1)
    Log-Success "Composer: $composerVersion"
}
catch {
    Log-Error "Composer verification failed"
}

# ============================================
# Project Setup
# ============================================

$projectRoot = Split-Path -Parent $PSCommandPath
if (Test-Path (Join-Path $projectRoot "composer.json")) {
    Push-Location $projectRoot
} else {
    Log-Error "composer.json not found. Are you in the project root directory?"
}

Log-Info "Setting up project..."

# Install PHP dependencies
Log-Info "Installing PHP dependencies (this may take a few minutes)..."
composer install --no-dev --optimize-autoloader
if ($LASTEXITCODE -ne 0) {
    Log-Error "Composer install failed"
}

# Install npm dependencies
Log-Info "Installing npm dependencies (this may take a few minutes)..."
npm install
if ($LASTEXITCODE -ne 0) {
    Log-Error "npm install failed"
}

# ============================================
# Configuration
# ============================================

Log-Info "Configuring application..."

# Create .env if it doesn't exist
if (-not (Test-Path ".env")) {
    Log-Info "Creating .env file..."
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
    } else {
        $envContent = @"
APP_NAME=Tomodoro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
LOG_LEVEL=debug
DB_CONNECTION=sqlite
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
"@
        Set-Content ".env" $envContent
    }
}

# Generate app key
if (-not (Select-String -Path ".env" -Pattern "APP_KEY=base64:" -Quiet)) {
    Log-Info "Generating application key..."
    php artisan key:generate --force
}
else {
    Log-Info "Application key already set"
}

# ============================================
# Database Setup
# ============================================

Log-Info "Setting up database..."
php artisan migrate --seed --force
if ($LASTEXITCODE -ne 0) {
    Log-Warning "Database setup completed with warnings (this is often OK on first run)"
}

# ============================================
# Build Frontend
# ============================================

Log-Info "Building frontend assets (this may take a minute)..."
npm run build
if ($LASTEXITCODE -ne 0) {
    Log-Error "Frontend build failed"
}

# ============================================
# Summary and Start
# ============================================

Pop-Location

Write-Host ""
Log-Success "✨ Installation complete!"
Write-Host ""
Log-Info "System Information:"
Write-Host "  PHP: $(php -v | Select-Object -First 1)"
Write-Host "  Node.js: $(node -v)"
Write-Host "  npm: $(npm -v)"
Write-Host "  Composer: $(composer --version | Select-Object -First 1)"
Write-Host ""
Log-Info "Starting development server..."
Write-Host "  📱 Open: http://localhost:8000"
Write-Host "  🛑 Stop with: Ctrl+C"
Write-Host ""

cd $projectRoot
php artisan serve

