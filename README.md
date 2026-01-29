# 📱 Tomodoro

**Веб-приложение для управления задачами с методикой Pomodoro Technique**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.0-red)](https://laravel.com)
[![Node.js Version](https://img.shields.io/badge/Node.js-20%2B-green)](https://nodejs.org)
[![Status](https://img.shields.io/badge/Status-Stable-brightgreen)](.)

**[📚 Полная документация](docs/INDEX.md)** • **[🚀 Начать за 2 минуты](#-быстрый-старт)** • **[📖 Полное руководство](docs/USER_MANUAL.md)**

> 🆕 **Новичок?** Начните с [docs/START_HERE.md](docs/START_HERE.md)

---

## 📖 Оглавление

- [О проекте](#-о-проекте)
- [Функциональность](#-функциональность)
- [Быстрый старт](#-быстрый-старт)
- [Установка](#-установка)
- [Документация](#-документация)
- [Требования](#-требования)
- [Лицензия](#-лицензия)


---

## 🎯 О проекте

**Tomodoro** — это веб-приложение для повышения производительности, объединяющее:

- 🎯 **Pomodoro Technique** — система управления временем с перерывами
- ✅ **Управление задачами** — создание, категоризация и отслеживание дел  
- 📅 **Календарь** — визуализация активности и планирование
- 📊 **Статистика** — аналитика производительности

### Для кого?

- 💼 **Профессионалы** — улучшение фокусировки
- 🎓 **Студенты** — эффективная подготовка
- 🏠 **Фрилансеры** — контроль времени проектов
- 👥 **Все** — кто хочет повысить производительность

---

## ✨ Функциональность

| Функция | Описание |
|---------|---------|
| ⏱ **Taimer** | Настраиваемые интервалы, уведомления, отслеживание сессий |
| ✅ **Задачи** | Создание, категоризация, приоритизация, сроки |
| 📅 **Календарь** | Визуализация активности за день/неделю/месяц |
| 📈 **Статистика** | Графики прогресса, достижения, экспорт данных |
| 🔐 **Безопасность** | Шифрование паролей, HTTPS, API аутентификация |
| 📱 **PWA** | Мобильное приложение, работает оффлайн |

---

## 🚀 Быстрый старт

### Windows
```bash
install.bat
```
Двойной клик → готово за 2 минуты

### Linux / macOS
```bash
chmod +x install.sh && ./install.sh
```
Одна команда → готово за 2 минуты

**Затем откройте**: http://localhost:8000

Подробнее: [QUICK_SETUP.md](docs/QUICK_SETUP.md)

---

## 📋 Требования

### Для пользователей
- ✅ Современный браузер (Chrome, Firefox, Safari, Edge)
- ✅ Интернет соединение

### Для разработчиков
- **PHP 8.2+**
- **Node.js 20+ LTS**
- **Composer 2.5+**
- **npm 8.0+**

Подробнее: [SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md)

---

## 🔧 Установка

### Вариант 1: Автоматическая (рекомендуется)

**Windows**: Двойной клик `install.bat`  
**Linux/macOS**: `chmod +x install.sh && ./install.sh`

### Вариант 2: Пошаговая

```bash
# 1. Установка зависимостей
composer install
npm install

# 2. Конфигурация
cp .env.example .env
php artisan key:generate

# 3. База данных
php artisan migrate

# 4. Запуск
npm run build
php artisan serve
```

Подробнее: [INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)

---

## 📚 Документация

Выберите нужный документ:

### 👤 Для пользователей
- **[USER_MANUAL.md](docs/USER_MANUAL.md)** — Полное руководство с FAQ и примерами

### 🔧 Для администраторов и разработчиков
- **[INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)** — Установка, конфигурация, deployment
- **[TECHNICAL_OVERVIEW.md](docs/TECHNICAL_OVERVIEW.md)** — Архитектура и стек
- **[API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)** — REST API endpoints
- **[SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md)** — Требования к системе

### 📖 Навигация
**[📚 ПОЛНЫЙ СПРАВОЧНИК ДОКУМЕНТАЦИИ](docs/INDEX.md)**
---

## 📁 Структура проекта

```
tomodoro/
│
├── 📖 README.md            ← Главная страница (ВЫ ЗДЕСЬ)
├── 🚀 install.bat          ← Установка Windows (двойной клик)
├── 🚀 install.sh           ← Установка Linux/macOS (одна команда)
├── 📦 composer.json        ← PHP зависимости
├── 📦 package.json         ← JavaScript зависимости
│
├── 📁 docs/                ← ВСЯ ДОКУМЕНТАЦИЯ
│   ├── 📚 INDEX.md         ← Справочник документации (начните отсюда!)
│   ├── 🚀 START_HERE.md    ← Для новичков
│   ├── 📖 USER_MANUAL.md   ← Полное руководство пользователя
│   ├── 📋 QUICK_SETUP.md   ← Быстрая установка (2 мин)
│   ├── 📋 INSTALLATION_GUIDE.md ← Установка и конфигурация
│   ├── 📋 SYSTEM_REQUIREMENTS.md ← Требования к системе
│   ├── 💻 TECHNICAL_OVERVIEW.md ← Архитектура и стек
│   ├── 🔌 API_DOCUMENTATION.md ← REST API endpoints
│   ├── ℹ️ LICENSE_AND_SUPPORT.md ← Лицензия и контакты
│   ├── 📝 DOCUMENTATION.md ← История переструктуризации
│   ├── 📝 CLEANUP_REPORT.md ← Отчет об очистке
│   ├── 📝 FINAL_SUMMARY.md ← Полный отчет
│   └── 📝 GITHUB_SETUP.md  ← Настройка GitHub
│
├── 📁 app/                 ← Основной код приложения
│   ├── Http/              # Controllers, Requests, Resources
│   ├── Livewire/          # Reactive компоненты
│   ├── Models/            # User, Task, PomodoroSession, etc
│   ├── Services/          # Business logic
│   └── Providers/         # Service Providers
├── 📁 config/             ← Конфигурация
├── 📁 database/           ← Миграции, factories, seeders
├── 📁 resources/          ← Frontend (views, CSS, JS)
├── 📁 routes/             ← web.php, api.php
├── 📁 tests/              ← Unit и Feature тесты
├── 📁 storage/            ← Логи, кэш
├── 📁 public/             ← Публичная папка
├── 📁 vendor/             ← Composer зависимости
└── 📁 node_modules/       ← npm зависимости
```

**Ключевое правило:** 
- 📄 В **корне** — только `README.md`
- 📚 В **docs/** — вся остальная документация

---

## 💻 Для разработчиков

### Команды разработки

```bash
# Запуск тестов
php artisan test

# Запуск всех компонентов
composer dev

# Запуск только фронтенда (hot reload)
npm run dev

# Сборка для production
npm run build

# Стилизация кода
composer run pint

# IDE helper
php artisan ide-helper:generate
```

### Создание новых компонентов

```bash
# Контроллер
php artisan make:controller TaskController

# Модель с миграцией
php artisan make:model Task -m

# Livewire компонент
php artisan make:livewire MyComponent

# Миграция
php artisan make:migration create_users_table
```

### API

REST API доступен по `/api/v1`

```bash
# Пример запроса
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/v1/tasks

# Создать задачу
curl -X POST http://localhost:8000/api/v1/tasks \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Задача","priority":"high"}'
```

Полная документация: [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)

---

## 🐳 Docker

```bash
# Сборка
docker build -t tomodoro:latest .

# Запуск
docker run -p 8000:8000 -e DB_CONNECTION=sqlite tomodoro:latest

# С Docker Compose
docker-compose up -d
```

---

## 📦 Лицензия

MIT License — см. [LICENSE](LICENSE)

```
MIT License
Copyright (c) 2026 Tomodoro Project

Permission is hereby granted, free of charge, to any person obtaining a copy...
```

---

## 🤝 Вклад

Помогайте развивать проект:

1. **Fork** репозиторий
2. Создайте ветку: `git checkout -b feature/my-feature`
3. Сделайте изменения и тесты
4. Push: `git push origin feature/my-feature`
5. Откройте **Pull Request**

Подробно: [INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)

---

## 💬 Поддержка

- 📖 **Документация**: [docs/INDEX.md](docs/INDEX.md)
- ❓ **FAQ**: [USER_MANUAL.md](docs/USER_MANUAL.md#6-часто-задаваемые-вопросы-faq)
- 🐛 **Ошибки**: GitHub Issues
- 💁 **Вопросы**: GitHub Discussions

---

## 🗺️ Дорожная карта

- ✅ **v1.0** (текущая) — базовая функциональность
- 📅 **v1.1** — Google Calendar интеграция, мобильное приложение
- 📅 **v1.2** — AI рекомендации, Slack интеграция

---

<div align="center">

### ⭐ Нравится проект? Поставьте звезду! ⭐

Made with ❤️ by Tomodoro Team

</div>
