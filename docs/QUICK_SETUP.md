# Быстрая установка Tomodoro

## 🚀 One-Liner Setup

Самый быстрый способ - одна команда. Работает на **полностью голых системах** (без установленных PHP, Node.js, Composer).

### 🍎 macOS (Homebrew установится автоматически)
```bash
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```

### 🐧 Linux (Ubuntu, Debian, Fedora, Arch, CentOS)
```bash
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```
> Скрипт попросит пароль для `sudo` при установке системных пакетов

### 🪟 Windows (PowerShell as Administrator)
```powershell
# 1. Откройте PowerShell с правами администратора
# 2. Выполните:
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

---

## 📋 Что устанавливается?

| Этап | Что происходит |
|------|---------|
| 1️⃣ **Система** | Проверка и установка: PHP 8.2+, Node.js 20+, Composer |
| 2️⃣ **Пакеты** | Установка зависимостей: `composer install` + `npm install` |
| 3️⃣ **Конфиг** | Создание .env, генерация ключа приложения |
| 4️⃣ **БД** | Инициализация SQLite, миграции, тестовые данные |
| 5️⃣ **Фронтенд** | Сборка CSS и JavaScript (`npm run build`) |
| 6️⃣ **Запуск** | Запуск dev сервера на http://localhost:8000 |

---

## ✨ Преимущества этого подхода

- ⚡ **One-liner** — всё в одной команде
- 🤖 **Полностью автоматическая** — не требует ввода ничего лишнего
- 🔄 **Кроссплатформа** — Windows, Linux, macOS
- 🛡️ **Безопасная** — проверяет всё что нужно перед установкой
- 🧹 **Чистая** — без ненужного софта
- 👶 **Для начинающих** — работает из коробки

---

## 📌 Локальная установка (если скачали файлы)

### Linux / macOS
```bash
# Загрузили проект локально?
cd путь/к/tomodoro
chmod +x install-unix.sh
bash install-unix.sh
```

### Windows
```powershell
# Откройте PowerShell с правами администратора в папке проекта
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

---

## 🆘 Troubleshooting

### Windows: "Execution Policy" ошибка

**Проблема:**
```
File cannot be loaded because running scripts is disabled on this system.
```

**Решение:**
```powershell
# 1. Откройте PowerShell как администратор
# 2. Выполните:
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# 3. Согласитесь (введите Y)
# 4. Попробуйте снова:
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

### Linux/macOS: "Permission denied"

**Проблема:**
```bash
bash: ./install-unix.sh: Permission denied
```

**Решение:**
```bash
chmod +x install-unix.sh
bash install-unix.sh
```

### Все платформы: "Port 8000 already in use"

**Проблема:**
```
Address already in use 127.0.0.1:8000
```

**Решение:**
```bash
# Используйте другой порт:
php artisan serve --port=8001

# Затем откройте:
# http://localhost:8001
```

### Linux/macOS: "command not found: curl"

**Решение:**
```bash
# Ubuntu/Debian
sudo apt-get install curl

# Fedora
sudo dnf install curl

# macOS (обычно есть встроенный)
brew install curl
```

### Windows: "choco command not found"

**Решение:**
Chocolatey был установлен, но PATH не обновлён. Закройте PowerShell и откройте заново как администратор.

### Linux: "sudo: command not found"

**Решение:**
На некоторых минимальных Linux установках `sudo` не предустановлен.
```bash
# Переключитесь на root пользователя:
su -

# Затем запустите скрипт заново:
bash install-unix.sh
```

### macOS: "Command not found: brew"

**Решение:**
```bash
# Переустановите Homebrew:
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Добавьте brew в PATH:
export PATH="/opt/homebrew/bin:$PATH"

# Затем запустите установку:
bash install-unix.sh
```

### "PHP/Node.js version too old"

**Проблема:**
```
PHP 7.4 found, but 8.2+ required
```

**Решение:**
```bash
# macOS
brew upgrade php

# Ubuntu/Debian
sudo apt-get install php8.2

# Fedora
sudo dnf install php8.2

# После этого может потребоваться перезагрузка терминала
```

### "composer install failed"

**Проблема:**
Ошибки при установке PHP зависимостей

**Попытайтесь:**
```bash
# Очистите кеш composer
composer clear-cache

# Установите заново
composer install --no-dev

# Если всё ещё ошибка, посмотрите логи
cat storage/logs/laravel.log
```

### "npm install failed"

**Проблема:**
Ошибки при установке JavaScript зависимостей

**Попытайтесь:**
```bash
# Удалите node_modules и package-lock.json
rm -rf node_modules package-lock.json

# Установите заново
npm install --legacy-peer-deps

# Собери фронтенд
npm run build
```

### "database.sqlite not found"

**Проблема:**
Ошибка при миграции БД

**Решение:**
```bash
# Создайте БД вручную
touch storage/app/database.sqlite

# Запустите миграции
php artisan migrate --seed
```

### Port нужно выбрать вручную

**Решение:**
```bash
# Найдите свободный порт:
# macOS/Linux:
lsof -i -P -n | grep LISTEN

# Windows (PowerShell):
Get-NetTCPConnection | Select LocalAddress, LocalPort | Where LocalPort -like "800*"

# Используйте свободный порт:
php artisan serve --port=8888
```

---

## ✅ Проверка установки

После завершения скрипта, проверьте что всё работает:

```bash
# 1. Откройте браузер
# http://localhost:8000

# 2. Создайте аккаунт и залогинитесь

# 3. Добавьте задачу и запустите таймер

# 4. Если ошибки - посмотрите логи:
tail -f storage/logs/laravel.log  # Linux/macOS
Get-Content storage/logs/laravel.log -Tail 50 -Wait  # Windows
```

---

## 📚 Дальше

- 📖 [USER_MANUAL.md](USER_MANUAL.md) — как пользоваться
- 🔧 [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) — подробная установка для production
- 💻 [TECHNICAL_OVERVIEW.md](TECHNICAL_OVERVIEW.md) — архитектура проекта
- 📚 [INDEX.md](INDEX.md) — полный справочник документации

---

## 🆘 Troubleshooting

### Windows: "Execution Policy" ошибка
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Linux: "Permission denied"
```bash
chmod +x install-unix.sh && bash install-unix.sh
```

### macOS: "Permission denied"
```bash
sudo chmod +x install-unix.sh && bash install-unix.sh
```

### Git не найден?
**macOS:** `brew install git`  
**Linux (Ubuntu):** `sudo apt-get install git`  
**Windows:** https://git-scm.com/download/win

### Linux / macOS

Если скрипт не запускается:
```bash
bash install.sh
```

Если нужны права sudo:
```bash
sudo bash install.sh
```

---

## 📝 После установки

Приложение будет доступно по адресу: **http://localhost:8000**

Для остановки сервера нажмите: **Ctrl+C**

Чтобы запустить снова в следующий раз:
- **Windows**: двойной клик на `install.bat`
- **Linux/macOS**: `php artisan serve`
