# 🚀 Tomodoro Installation Guide

> Полная автоматическая установка на любой системе (с нуля)

## Выберите вашу ОС:

### 🍎 macOS & Linux
```bash
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```

### 🪟 Windows (PowerShell)
1. Откройте **PowerShell как администратор**
2. Выполните команду:
```powershell
powershell -ExecutionPolicy Bypass -File install-windows.ps1
```

---

## ✨ Что происходит при установке?

### 🔧 Система (автоматически устанавливается если нужна)
- PHP 8.2+ 
- Node.js 20+
- Composer
- npm

### 📦 Проект
- Установка зависимостей (Composer + npm)
- Создание конфигурации (.env)
- Инициализация БД (SQLite)
- Сборка фронтенда (CSS/JS)
- Запуск dev сервера

---

## ✅ После установки

Откройте в браузере: **http://localhost:8000**

---

## 📚 Дополнительная информация

| Документ | О чем |
|----------|-------|
| [📖 START_HERE.md](docs/START_HERE.md) | Первые шаги, структура проекта |
| [⚙️ QUICK_SETUP.md](docs/QUICK_SETUP.md) | Локальная установка, troubleshooting |
| [🛠️ INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) | Детальная инструкция по шагам |
| [📋 SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md) | Требования к системе |

---

## 🆘 Проблемы?

- **Windows**: Ошибка "Execution Policy"? → [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md#windows-execution-policy-ошибка)
- **Linux/macOS**: "Permission denied"? → [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md#linuxmacos-permission-denied)
- Общие вопросы? → [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)

---

**🎯 Самый быстрый способ:** Copy-paste одну из команд выше ☝️
