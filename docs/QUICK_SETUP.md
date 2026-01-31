# Быстрая установка Tomodoro

## 🚀 One-Liner Setup

### 🍎 macOS
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)"
```
**Или локально:**
```bash
bash <(curl -fsSL file://$(pwd)/install-unix.sh)
```

### 🐧 Linux (Ubuntu/Debian/Fedora/Arch)
```bash
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```

### 🪟 Windows (PowerShell)
```powershell
powershell -NoProfile -ExecutionPolicy Bypass -Command "& ([scriptblock]::Create((New-Object Net.WebClient).DownloadString('https://raw.githubusercontent.com/yourusername/tomodoro/main/install-windows.ps1')))"
```
**Или локально:**
```powershell
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

---

## 📋 Что делает установщик?

| Этап | Действие |
|------|---------|
| 🔧 **Зависимости** | Устанавливает PHP 8.2+, Node.js 20+, Composer |
| 📦 **Пакеты** | Запускает `composer install` и `npm install` |
| ⚙️ **Конфиг** | Создаёт `.env`, генерирует ключ приложения |
| 🗄️ **БД** | Мигрирует и заполняет БД тестовыми данными |
| 🎨 **Фронтенд** | Собирает Vite (npm run build) |
| 🚀 **Запуск** | Стартует сервер на **http://localhost:8000** |

---

## ✨ Преимущества

- ⚡ **Один скрипт** на все платформы - no BS
- 🔄 **Кроссплатформа** - Windows, Linux, macOS
- 🤖 **Full Automation** - установка → запуск за минуту
- 🛡️ **Безопасен** - не требует root (кроме установки зависимостей)
- 🧹 **Чистый** - без лишнего, только необходимое

---

## ⚠️ Требования

- **Git** - для клонирования проекта
- **curl** или **wget** - для скачивания скрипта
- **Права администратора** - для установки зависимостей (Windows) или sudo (Linux/macOS)

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
