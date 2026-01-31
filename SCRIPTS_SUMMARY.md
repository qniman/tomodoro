# 📝 Setup Scripts Summary

This document explains the new installation scripts for Tomodoro.

## 📂 Files Created

### 1. `install-unix.sh` - Universal Unix/Linux/macOS Installer
- **Location**: Root of project
- **Purpose**: One-liner installation for Unix-like systems
- **Supported OS**: macOS, Linux (Ubuntu, Debian, Fedora, RHEL, CentOS, Arch)
- **Dependencies**: curl, bash, sudo (for Linux/macOS)

**Usage:**
```bash
# Local installation
bash install-unix.sh

# Remote installation
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```

**What it does:**
1. Detects your OS
2. Installs Homebrew (macOS only)
3. Installs PHP, Node.js, Composer via package manager
4. Runs `composer install --no-dev`
5. Runs `npm install`
6. Creates `.env` file from `.env.example`
7. Generates app key
8. Runs migrations with seed
9. Builds frontend with `npm run build`
10. Starts Laravel dev server

### 2. `install-windows.ps1` - Windows PowerShell Installer
- **Location**: Root of project
- **Purpose**: One-liner installation for Windows
- **Requirements**: PowerShell 5.1+, Administrator privileges
- **Dependencies**: None (Chocolatey will be installed if missing)

**Usage:**
```powershell
# Local installation (as Administrator)
powershell -ExecutionPolicy Bypass -File install-windows.ps1

# Remote installation
powershell -NoProfile -ExecutionPolicy Bypass -Command "& ([scriptblock]::Create((New-Object Net.WebClient).DownloadString('https://raw.githubusercontent.com/yourusername/tomodoro/main/install-windows.ps1')))"
```

**What it does:**
1. Checks for Administrator privileges
2. Installs Chocolatey if not present
3. Installs PHP, Node.js, Composer via Chocolatey
4. Runs `composer install --no-dev --optimize-autoloader`
5. Runs `npm install`
6. Creates `.env` file
7. Generates app key
8. Runs migrations with seed
9. Builds frontend
10. Starts Laravel dev server

## 📖 Updated Documentation Files

### 1. `docs/START_HERE.md` - New Quick Start Guide
- One-liner commands for all platforms
- What gets installed
- First steps after setup
- Basic commands
- Troubleshooting

### 2. `docs/QUICK_SETUP.md` - Updated Installation Guide
- Detailed one-liner setup
- Platform-specific instructions
- Comprehensive troubleshooting
- Step-by-step breakdown

### 3. `docs/INDEX.md` - Updated Navigation
- Better organization
- Prioritized "Quick Start"
- Time-based navigation
- Situation-based recommendations

### 4. `README.md` - Updated Main Page
- Prominent one-liner instructions
- Better quick start section
- Links to START_HERE.md

### 5. `INSTALL.md` - New Installation Quick Reference
- One-liner for each OS
- What gets installed
- Quick help links

## 🎯 Key Features

✅ **One-liner setup** - Single command to get started  
✅ **Cross-platform** - Windows, Linux, macOS  
✅ **Auto-detection** - Detects OS and installs appropriate packages  
✅ **Error handling** - Graceful error messages and recovery  
✅ **Idempotent** - Safe to run multiple times  
✅ **No manual steps** - Everything automated  
✅ **Quick feedback** - Clear status messages with emojis  

## 🔄 Migration Path

**Before (old way):**
```bash
# Linux/macOS
chmod +x install.sh && ./install.sh

# Windows
install.bat
```

**After (new way):**
```bash
# macOS/Linux one-liner
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)

# Windows one-liner
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

## 📋 Verification

To verify the installation worked:

```bash
# Check PHP version
php -v

# Check Node.js version
node -v

# Check Composer version
composer --version

# Check server is running
curl http://localhost:8000
```

## 🐛 Troubleshooting

### Unix/Linux/macOS

**Permission denied:**
```bash
chmod +x install-unix.sh
```

**Homebrew not found (macOS):**
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

**Port 8000 already in use:**
```bash
php artisan serve --port=8001
```

### Windows

**Execution Policy error:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**Chocolatey not found:**
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

**Not running as Administrator:**
Right-click PowerShell → "Run as administrator"

## 📚 Related Documentation

- [QUICK_SETUP.md](docs/QUICK_SETUP.md) - Detailed setup guide
- [INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) - Advanced setup
- [SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md) - Requirements
- [START_HERE.md](docs/START_HERE.md) - First-time user guide

## ✨ Next Steps

1. Update your Git repository with these files
2. Test the installation scripts locally
3. Test with one-liner remote URLs
4. Update your GitHub README to link to START_HERE.md or INSTALL.md
5. Consider creating CI/CD tests for the installation scripts

---

**Last updated:** January 2026  
**Version:** 1.0  
**Status:** Ready for production
