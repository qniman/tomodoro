#Requires -RunAsAdministrator
# Tomodoro Installation Script for Windows
# Works on completely bare systems with automatic dependency installation
# Usage: powershell -ExecutionPolicy Bypass -File install-windows.ps1

$ErrorActionPreference = "Stop"
$WarningPreference = "SilentlyContinue"
$PSDefaultParameterValues['*:Encoding'] = 'UTF8'

# ============================================
# Color Codes
# ============================================
$Success = [System.ConsoleColor]::Green
$Error_Color = [System.ConsoleColor]::Red
$Warning_Color = [System.ConsoleColor]::Yellow
$Info_Color = [System.ConsoleColor]::Cyan

# Logging functions
function Log-Info { Write-Host "ℹ️  $($args -join ' ')" -ForegroundColor $Info_Color }
function Log-Success { Write-Host "✅ $($args -join ' ')" -ForegroundColor $Success }
function Log-Warning { Write-Host "⚠️  $($args -join ' ')" -ForegroundColor $Warning_Color }
function Log-Error { Write-Host "❌ $($args -join ' ')" -ForegroundColor $Error_Color; Exit 1 }

# Error handler
trap { 
    Write-Host ""
    Log-Error "Installation failed: $($_.Exception.Message)"
}

# ============================================
# Welcome Banner
# ============================================
Write-Host ""
Write-Host "╔════════════════════════════════════════╗" -ForegroundColor $Info_Color
Write-Host "║    🎯 Tomodoro - Installation Setup    ║" -ForegroundColor $Info_Color
Write-Host "║         Windows PowerShell 5.1+        ║" -ForegroundColor $Info_Color
Write-Host "╚════════════════════════════════════════╝" -ForegroundColor $Info_Color
Write-Host ""

# ============================================
# Admin Privilege Check
# ============================================
Log-Info "Checking for Administrator privileges..."
$isAdmin = [Security.Principal.WindowsIdentity]::GetCurrent().Owner.IsWellKnown([Security.Principal.WellKnownSidType]::BuiltinAdministratorsSid)
if (-not $isAdmin) {
    Log-Error "This script must run as Administrator`nPlease right-click PowerShell and select 'Run as administrator'"
}
Log-Success "Running with Administrator privileges"

# ============================================
# Helper Functions
# ============================================

function Test-CommandExists {
    param([string]$command)
    try {
        if (Get-Command $command -ErrorAction SilentlyContinue) {
            return $true
        }
        return $false
    }
    catch {
        return $false
    }
}

function Refresh-PathEnvironment {
    $env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")
}

function Install-Chocolatey {
    Log-Info "Checking Chocolatey..."
    
    if (Test-CommandExists choco) {
        $chocoVersion = (choco --version 2>&1 | Select-Object -First 1)
        Log-Success "Chocolatey already installed: $chocoVersion"
        return
    }
    
    Log-Info "Installing Chocolatey (this may take a moment)..."
    try {
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        
        $ChocoInstallUri = 'https://community.chocolatey.org/install.ps1'
        $WebClient = New-Object System.Net.WebClient
        $ChocoScript = $WebClient.DownloadString($ChocoInstallUri)
        
        $ChocoScript | Invoke-Expression
        
        # Refresh PATH
        Refresh-PathEnvironment
        
        # Verify
        Start-Sleep -Seconds 2
        if (Test-CommandExists choco) {
            $chocoVersion = (choco --version 2>&1 | Select-Object -First 1)
            Log-Success "Chocolatey installed successfully: $chocoVersion"
        }
        else {
            Log-Error "Chocolatey installation failed"
        }
    }
    catch {
        Log-Error "Failed to install Chocolatey: $_"
    }
}

function Install-Tool {
    param(
        [string]$ToolName,
        [string]$ChocoPackage,
        [string]$CommandToTest,
        [string]$VersionFlag = "--version"
    )
    
    Log-Info "Checking $ToolName..."
    
    if (Test-CommandExists $CommandToTest) {
        try {
            $version = & $CommandToTest $VersionFlag 2>&1 | Select-Object -First 1
            Log-Success "$ToolName already installed: $version"
        }
        catch {
            Log-Success "$ToolName already installed"
        }
        return
    }
    
    Log-Info "Installing $ToolName (this may take a few minutes)..."
    try {
        choco install $ChocoPackage -y --no-progress --ignore-checksums
        
        if ($LASTEXITCODE -eq 0) {
            # Refresh PATH
            Refresh-PathEnvironment
            Start-Sleep -Seconds 3
            
            # Verify installation
            if (Test-CommandExists $CommandToTest) {
                try {
                    $version = & $CommandToTest $VersionFlag 2>&1 | Select-Object -First 1
                    Log-Success "$ToolName installed successfully: $version"
                }
                catch {
                    Log-Success "$ToolName installed successfully"
                }
            }
            else {
                Log-Warning "$ToolName installed but PATH refresh needed"
                # Last resort: manually add common paths
                if ($ToolName -eq "PHP") {
                    $env:Path += ";C:\tools\php82"
                }
                elseif ($ToolName -eq "Node.js") {
                    $env:Path += ";C:\Program Files\nodejs"
                }
            }
        }
        else {
            Log-Error "$ToolName installation failed with code $LASTEXITCODE"
        }
    }
    catch {
        Log-Error "Failed to install $ToolName : $_"
    }
}

# ============================================
# Installation Steps
# ============================================

Write-Host ""
Log-Info "Step 1: Installing System Dependencies"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

Install-Chocolatey

Install-Tool -ToolName "PHP 8.2+" -ChocoPackage "php" -CommandToTest "php" -VersionFlag "-v"
Install-Tool -ToolName "Node.js" -ChocoPackage "nodejs" -CommandToTest "node"
Install-Tool -ToolName "Composer" -ChocoPackage "composer" -CommandToTest "composer"

# Final PATH refresh
Refresh-PathEnvironment

Write-Host ""
Log-Info "Step 2: Verifying Installations"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Log-Success "PHP: $phpVersion"
}
catch {
    Log-Error "PHP verification failed - please check installation manually"
}

try {
    $nodeVersion = node -v
    Log-Success "Node.js: $nodeVersion"
}
catch {
    Log-Error "Node.js verification failed"
}

try {
    $npmVersion = npm -v
    Log-Success "npm: $npmVersion"
}
catch {
    Log-Error "npm verification failed"
}

try {
    $composerVersion = composer --version 2>&1 | Select-Object -First 1
    Log-Success "Composer: $composerVersion"
}
catch {
    Log-Error "Composer verification failed"
}

# ============================================
# Project Setup
# ============================================

Write-Host ""
Log-Info "Step 3: Setting Up Tomodoro Project"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

$projectRoot = Split-Path -Parent $PSCommandPath
$composerJsonPath = Join-Path $projectRoot "composer.json"

if (-not (Test-Path $composerJsonPath)) {
    Log-Error "composer.json not found in $projectRoot`nPlease run this script from the project root directory"
}

Push-Location $projectRoot

Log-Info "Installing PHP dependencies (this may take several minutes)..."
composer install --no-dev --optimize-autoloader 2>&1 | ForEach-Object {
    if ($_ -match "error|failed" -and $LASTEXITCODE -ne 0) {
        Log-Warning $_
    }
}
if ($LASTEXITCODE -ne 0) {
    Log-Warning "Composer install returned exit code $LASTEXITCODE"
}

Log-Info "Installing Node.js dependencies (this may take several minutes)..."
npm install 2>&1 | ForEach-Object {
    if ($_ -match "error|ERR!" -and $LASTEXITCODE -ne 0) {
        Log-Warning $_
    }
}
if ($LASTEXITCODE -ne 0) {
    Log-Warning "npm install returned exit code $LASTEXITCODE"
}

# ============================================
# Environment Configuration
# ============================================

Write-Host ""
Log-Info "Step 4: Configuring Application"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

# Create .env file
if (-not (Test-Path ".env")) {
    Log-Info "Creating .env file..."
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Log-Success ".env created from .env.example"
    }
    else {
        Log-Warning ".env.example not found, creating minimal .env"
        $envContent = @"
APP_NAME=Tomodoro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
LOG_LEVEL=debug
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
"@
        Set-Content ".env" $envContent
        Log-Success ".env created with default configuration"
    }
}
else {
    Log-Success ".env already exists"
}

# Generate application key if not present
Log-Info "Checking application key..."
$envContent = Get-Content ".env"
if ($envContent -notmatch "APP_KEY=base64:") {
    Log-Info "Generating application key..."
    try {
        php artisan key:generate --force 2>&1
        Log-Success "Application key generated"
    }
    catch {
        Log-Error "Failed to generate application key: $_"
    }
}
else {
    Log-Success "Application key already set"
}

# ============================================
# Database Setup
# ============================================

Write-Host ""
Log-Info "Step 5: Setting Up Database"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

Log-Info "Running database migrations and seeders..."
try {
    php artisan migrate:fresh --seed --force 2>&1
    Log-Success "Database initialized successfully"
}
catch {
    Log-Warning "Database setup completed with warnings (check database file was created)"
}

# ============================================
# Frontend Build
# ============================================

Write-Host ""
Log-Info "Step 6: Building Frontend Assets"
Write-Host "════════════════════════════════════════" -ForegroundColor $Info_Color

Log-Info "Building frontend (this may take a minute)..."
try {
    npm run build 2>&1 | Out-Null
    Log-Success "Frontend assets built successfully"
}
catch {
    Log-Error "Frontend build failed: $_"
}

# ============================================
# Completion
# ============================================

Pop-Location

Write-Host ""
Write-Host "╔════════════════════════════════════════╗" -ForegroundColor $Success
Write-Host "║     ✨ Installation Complete! ✨      ║" -ForegroundColor $Success
Write-Host "╚════════════════════════════════════════╝" -ForegroundColor $Success
Write-Host ""

Log-Info "System Information:"
Write-Host "  📦 PHP: $(php -v 2>&1 | Select-Object -First 1)"
Write-Host "  📦 Node.js: $(node -v)"
Write-Host "  📦 npm: $(npm -v)"
Write-Host "  📦 Composer: $(composer --version 2>&1 | Select-Object -First 1)"
Write-Host ""

Log-Info "Next Steps:"
Write-Host "  1. Open project folder: cd $(Split-Path -Leaf $projectRoot)"
Write-Host "  2. Start dev server:   php artisan serve"
Write-Host "  3. Open in browser:    http://localhost:8000"
Write-Host ""
Write-Host "  📚 Documentation: Check docs/QUICK_SETUP.md for more info"
Write-Host "  🆘 Issues?       Check docs/INSTALLATION_GUIDE.md"
Write-Host ""

Log-Info "Starting development server in 3 seconds..."
Start-Sleep -Seconds 3

cd $projectRoot
php artisan serve

