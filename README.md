# 📱 Tomodoro

**Веб-приложение для управления задачами с методикой Pomodoro Technique**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.0-red)](https://laravel.com)
[![Node.js Version](https://img.shields.io/badge/Node.js-20%2B-green)](https://nodejs.org)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen)](.)

**[🚀 Установка](INSTALL.md)** • **[📚 Документация](docs/INDEX.md)** • **[👤 Руководство](docs/START_HERE.md)**

---

## 🎯 О проекте

**Tomodoro** — веб-приложение для повышения производительности:

- ⏱️ **Pomodoro Technique** — система управления временем с перерывами
- ✅ **Управление задачами** — создание, категоризация и отслеживание дел
- 📅 **Календарь** — визуализация активности и планирование
- 📊 **Статистика** — аналитика производительности

### Для кого?

- 💼 Профессионалы — улучшение фокусировки
- 🎓 Студенты — эффективная подготовка
- 🏠 Фрилансеры — контроль времени проектов

---

## ✨ Функциональность

| Функция | Описание |
|---------|---------|
| ⏱️ **Timer** | Настраиваемые интервалы, уведомления, отслеживание |
| ✅ **Задачи** | Создание, категоризация, приоритизация, сроки |
| 📅 **Календарь** | Визуализация активности за день/неделю/месяц |
| 📈 **Статистика** | Графики прогресса, достижения, экспорт данных |
| 🔐 **Безопасность** | Шифрование паролей, HTTPS, аутентификация |
| 📱 **PWA** | Мобильное приложение, работает оффлайн |

---

## 🚀 Быстрая установка (One-Liner)

### Все ОС (Windows, macOS, Linux):
```bash
# Windows: PowerShell как администратор
powershell -ExecutionPolicy Bypass -File install-windows.ps1

# macOS & Linux
bash <(curl -fsSL https://raw.githubusercontent.com/yourusername/tomodoro/main/install-unix.sh)
```

**Откройте**: http://localhost:8000 🎉

Детали: [INSTALL.md](INSTALL.md)

---

## 📋 Требования

### Для пользователей
- Современный браузер (Chrome, Firefox, Safari, Edge)

### Для разработчиков
- PHP 8.2+
- Node.js 20+ LTS
- Composer 2.5+
- npm 8.0+

---

## 📁 Структура проекта

```
tomodoro/
├── INSTALL.md           ← Установка (начните отсюда!)
├── composer.json        ← PHP зависимости
├── package.json         ← JavaScript зависимости
├── install-windows.ps1  ← Windows установка
├── install-unix.sh      ← Linux/macOS установка
│
├── docs/                ← Документация
│   ├── INDEX.md         ← Справочник
│   ├── START_HERE.md    ← Для новичков
│   ├── QUICK_SETUP.md   ← Быстрая установка
│   ├── INSTALLATION_GUIDE.md ← Детальная инструкция
│   ├── SYSTEM_REQUIREMENTS.md ← Требования
│   ├── USER_MANUAL.md   ← Руководство пользователя
│   ├── API_DOCUMENTATION.md ← REST API
│   └── TECHNICAL_OVERVIEW.md ← Архитектура
│
├── app/                 ← Приложение
│   ├── Http/           # Controllers, Requests, Resources
│   ├── Livewire/       # Reactive компоненты
│   ├── Models/         # User, Task, PomodoroSession
│   └── Services/       # Business logic
├── config/             ← Конфигурация
├── database/           ← Миграции, factories
├── resources/          ← Frontend (views, CSS, JS)
├── routes/             ← web.php, api.php
├── tests/              ← Тесты
└── public/             ← Публичная папка
```

---

## 💻 Команды разработки

```bash
# Установка зависимостей
composer install
npm install

# Запуск dev сервера
php artisan serve

# Запуск тестов
php artisan test

# Запуск фронтенда с hot reload
npm run dev

# Сборка для production
npm run build

# Стилизация кода
composer run pint
```

---

## 🔌 API

REST API доступен по `/api/v1`

```bash
# Список задач
curl http://localhost:8000/api/v1/tasks

# Создать задачу
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Моя задача","priority":"high"}'
```

Полная документация: [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)

---

## 📚 Документация

| Ссылка | Для |
|--------|------|
| [🚀 INSTALL.md](INSTALL.md) | Установка |
| [📖 START_HERE.md](docs/START_HERE.md) | Новичков |
| [👤 USER_MANUAL.md](docs/USER_MANUAL.md) | Пользователей |
| [⚙️ QUICK_SETUP.md](docs/QUICK_SETUP.md) | Быстрой установки |
| [🛠️ INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) | Администраторов |
| [💻 TECHNICAL_OVERVIEW.md](docs/TECHNICAL_OVERVIEW.md) | Разработчиков |
| [🔌 API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md) | API интеграции |

---

## 📦 Лицензия

MIT License — см. [LICENSE](LICENSE)

---

## 🤝 Вклад

1. Fork репозиторий
2. Создайте ветку: `git checkout -b feature/my-feature`
3. Commit: `git commit -am 'Add feature'`
4. Push: `git push origin feature/my-feature`
5. Pull Request

---

## 💬 Поддержка

- 📚 [Документация](docs/INDEX.md)
- ❓ [START_HERE.md](docs/START_HERE.md) для новичков
- 🐛 [GitHub Issues](https://github.com/yourusername/tomodoro/issues)

---

<div align="center">

**⭐ Нравится проект? Поставьте звезду! ⭐**

Made with ❤️ by Tomodoro Team

</div>
