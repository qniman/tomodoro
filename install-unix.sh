#!/bin/bash
# Tomodoro One-Liner Install - Unix/Linux/macOS
# Usage: bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)

set -e
trap 'echo "❌ Ошибка установки!"; exit 1' ERR

echo "🎯 Tomodoro Setup"
echo "================="

# Determine OS
OS=$(uname -s | tr '[:upper:]' '[:lower:]')
DISTRO=""

echo "🔍 Detected OS: $OS"

# Install dependencies based on OS
case "$OS" in
  darwin)
    echo "🍎 Installing on macOS..."
    if ! command -v brew &> /dev/null; then
      echo "📦 Installing Homebrew..."
      /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" || true
    fi
    command -v php &> /dev/null || brew install php
    command -v node &> /dev/null || brew install node
    command -v composer &> /dev/null || brew install composer
    ;;
  linux)
    echo "🐧 Installing on Linux..."
    if [ -f /etc/os-release ]; then
      . /etc/os-release
      DISTRO=$ID
      case "$DISTRO" in
        ubuntu|debian)
          echo "Installing Debian packages..."
          sudo apt-get update
          dpkg -l | grep -q php || sudo apt-get install -y php php-cli php-curl php-json php-sqlite3
          dpkg -l | grep -q nodejs || sudo apt-get install -y nodejs npm
          dpkg -l | grep -q composer || sudo apt-get install -y composer
          ;;
        fedora|rhel|centos)
          echo "Installing RedHat packages..."
          rpm -q php || sudo dnf install -y php php-cli php-curl php-json php-pdo
          rpm -q nodejs || sudo dnf install -y nodejs npm
          rpm -q composer || sudo dnf install -y composer
          ;;
        arch)
          echo "Installing Arch packages..."
          pacman -Q php || sudo pacman -S --noconfirm php
          pacman -Q nodejs || sudo pacman -S --noconfirm nodejs npm
          pacman -Q composer || sudo pacman -S --noconfirm composer
          ;;
      esac
    fi
    ;;
esac

echo "✅ Dependencies installed"
echo "📦 Installing project dependencies..."
composer install --no-dev --optimize-autoloader || true
npm install

echo "⚙️ Configuring environment..."
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
  else
    echo "APP_KEY=base64:$(openssl rand -base64 32)" > .env
  fi
fi

echo "🔑 Generating app key..."
php artisan key:generate --force || true

echo "🗄️ Setting up database..."
php artisan migrate --seed --force || true

echo "🎨 Building frontend..."
npm run build

echo ""
echo "✅ Installation complete!"
echo "🚀 Starting server..."
echo "📱 Open http://localhost:8000"
echo ""
php artisan serve --host=0.0.0.0

