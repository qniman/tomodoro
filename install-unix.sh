#!/bin/bash
# Tomodoro One-Liner Install - Unix/Linux/macOS
# Works on completely bare systems with automatic dependency installation
# Usage: bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Logging functions
log() { echo -e "${BLUE}ℹ️  $1${NC}"; }
success() { echo -e "${GREEN}✅ $1${NC}"; }
error() { echo -e "${RED}❌ $1${NC}"; exit 1; }
warning() { echo -e "${YELLOW}⚠️  $1${NC}"; }

# Error handler
trap 'error "Installation failed at line $LINENO"' ERR

echo ""
echo "🎯 Tomodoro Setup - Complete Installation"
echo "=========================================="
echo ""

# Determine OS
OS=$(uname -s | tr '[:upper:]' '[:lower:]')
DISTRO=""

log "Detected OS: $OS"

# Helper function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Helper function to install with retry
install_with_retry() {
    local max_attempts=3
    local attempt=1
    while [ $attempt -le $max_attempts ]; do
        if "$@"; then
            return 0
        fi
        warning "Attempt $attempt failed, retrying..."
        ((attempt++))
        sleep 2
    done
    error "Failed to install after $max_attempts attempts"
}

# ============================================
# OS Detection and Dependency Installation
# ============================================

case "$OS" in
  darwin)
    log "Installing on macOS..."
    
    # Check for and install Homebrew
    if ! command_exists brew; then
      log "Homebrew not found, installing..."
      install_with_retry /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
      # Add brew to PATH for this session
      if [ -x /opt/homebrew/bin/brew ]; then
        export PATH="/opt/homebrew/bin:$PATH"
      fi
    fi
    success "Homebrew ready"
    
    # Install PHP
    if ! command_exists php; then
      log "Installing PHP..."
      install_with_retry brew install php
    else
      log "PHP already installed: $(php -v | head -1)"
    fi
    
    # Install Node.js
    if ! command_exists node; then
      log "Installing Node.js..."
      install_with_retry brew install node
    else
      log "Node.js already installed: $(node -v)"
    fi
    
    # Install Composer
    if ! command_exists composer; then
      log "Installing Composer..."
      install_with_retry brew install composer
    else
      log "Composer already installed: $(composer --version | head -1)"
    fi
    ;;
    
  linux)
    log "Installing on Linux..."
    
    if [ -f /etc/os-release ]; then
      . /etc/os-release
      DISTRO=$ID
      
      case "$DISTRO" in
        ubuntu|debian)
          log "Detected: Debian/Ubuntu"
          
          # Update package lists
          log "Updating package lists (may require sudo)..."
          if sudo -n true 2>/dev/null; then
            sudo apt-get update
          else
            warning "sudo password may be requested"
            sudo apt-get update
          fi
          
          # Install PHP
          if ! command_exists php; then
            log "Installing PHP..."
            sudo apt-get install -y php php-cli php-curl php-json php-sqlite3 php-mbstring php-xml php-bcmath
          else
            log "PHP already installed: $(php -v | head -1)"
          fi
          
          # Install Node.js and npm
          if ! command_exists node; then
            log "Installing Node.js and npm..."
            sudo apt-get install -y nodejs npm
          else
            log "Node.js already installed: $(node -v)"
          fi
          
          # Install Composer
          if ! command_exists composer; then
            log "Installing Composer..."
            sudo apt-get install -y composer
          else
            log "Composer already installed: $(composer --version | head -1)"
          fi
          
          # Install git (often needed)
          if ! command_exists git; then
            log "Installing Git..."
            sudo apt-get install -y git
          fi
          ;;
          
        fedora|rhel|centos)
          log "Detected: RedHat/Fedora"
          
          log "Updating package lists..."
          sudo dnf check-update || true
          
          # Install PHP
          if ! command_exists php; then
            log "Installing PHP..."
            sudo dnf install -y php php-cli php-curl php-json php-pdo php-sqlite php-mbstring php-xml php-bcmath
          else
            log "PHP already installed: $(php -v | head -1)"
          fi
          
          # Install Node.js and npm
          if ! command_exists node; then
            log "Installing Node.js and npm..."
            sudo dnf install -y nodejs npm
          else
            log "Node.js already installed: $(node -v)"
          fi
          
          # Install Composer
          if ! command_exists composer; then
            log "Installing Composer..."
            sudo dnf install -y composer
          else
            log "Composer already installed: $(composer --version | head -1)"
          fi
          
          # Install git
          if ! command_exists git; then
            log "Installing Git..."
            sudo dnf install -y git
          fi
          ;;
          
        arch)
          log "Detected: Arch Linux"
          
          log "Updating package lists..."
          sudo pacman -Sy || true
          
          # Install PHP
          if ! command_exists php; then
            log "Installing PHP..."
            sudo pacman -S --noconfirm php php-curl php-sqlite
          else
            log "PHP already installed: $(php -v | head -1)"
          fi
          
          # Install Node.js and npm
          if ! command_exists node; then
            log "Installing Node.js and npm..."
            sudo pacman -S --noconfirm nodejs npm
          else
            log "Node.js already installed: $(node -v)"
          fi
          
          # Install Composer
          if ! command_exists composer; then
            log "Installing Composer..."
            sudo pacman -S --noconfirm composer
          else
            log "Composer already installed: $(composer --version | head -1)"
          fi
          
          # Install git
          if ! command_exists git; then
            log "Installing Git..."
            sudo pacman -S --noconfirm git
          fi
          ;;
          
        *)
          error "Unsupported Linux distribution: $DISTRO. Please install PHP 8.2+, Node.js 20+, and Composer manually."
          ;;
      esac
    else
      error "Could not detect Linux distribution"
    fi
    ;;
    
  *)
    error "Unsupported OS: $OS"
    ;;
esac

# ============================================
# Verify Critical Dependencies
# ============================================

log "Verifying installed tools..."

if ! command_exists php; then
  error "PHP is not installed or not in PATH"
fi
PHP_VERSION=$(php -v | grep -oP '\d+\.\d+' | head -1)
success "PHP $PHP_VERSION"

if ! command_exists node; then
  error "Node.js is not installed or not in PATH"
fi
success "Node.js $(node -v)"

if ! command_exists npm; then
  error "npm is not installed or not in PATH"
fi
success "npm $(npm -v)"

if ! command_exists composer; then
  error "Composer is not installed or not in PATH"
fi
success "Composer $(composer --version | head -1)"

# ============================================
# Project Setup
# ============================================

log "Setting up project..."

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
  error "composer.json not found. Are you in the project root directory?"
fi

# Install PHP dependencies
log "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Install npm dependencies
log "Installing npm dependencies..."
npm install

# ============================================
# Configuration
# ============================================

log "Configuring application..."

if [ ! -f ".env" ]; then
  log "Creating .env file..."
  if [ -f ".env.example" ]; then
    cp .env.example .env
  else
    cat > .env << 'EOF'
APP_NAME=Tomodoro
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
LOG_CHANNEL=stack
LOG_LEVEL=debug
DB_CONNECTION=sqlite
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
EOF
  fi
fi

# Generate app key if not already set
if ! grep -q "APP_KEY=base64:" .env; then
  log "Generating application key..."
  php artisan key:generate --force
else
  log "Application key already set"
fi

# ============================================
# Database Setup
# ============================================

log "Setting up database..."
php artisan migrate --seed --force

# ============================================
# Build Frontend
# ============================================

log "Building frontend assets..."
npm run build

# ============================================
# Summary and Start
# ============================================

echo ""
success "✨ Installation complete!"
echo ""
log "System Information:"
echo "  PHP: $PHP_VERSION"
echo "  Node.js: $(node -v)"
echo "  npm: $(npm -v)"
echo "  Composer: $(composer --version | head -1)"
echo ""
log "Starting development server..."
echo "  📱 Open: http://localhost:8000"
echo "  🛑 Stop with: Ctrl+C"
echo ""

php artisan serve --host=0.0.0.0

