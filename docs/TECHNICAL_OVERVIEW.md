# Tomodoro - Техническое описание системы

## 1. Общее описание

**Tomodoro** — это веб-приложение для управления задачами и производительности, интегрирующее методику Pomodoro Technique для эффективного управления временем. Приложение позволяет пользователям создавать задачи, категоризировать их по тегам, отслеживать сессии работы через встроенный таймер и визуализировать данные в календаре.

**Назначение:** Повышение производительности пользователей путём контроля времени работы и структурированного управления задачами.

**Целевая аудитория:**
- Конечные пользователи: люди, желающие улучшить свою производительность
- Администраторы систем: для установки и поддержки на сервере
- Разработчики: для расширения функциональности

---

## 2. Архитектура системы

### 2.1 Структура решения

```
┌──────────────────────────────────────┐
│         Веб-браузер                  │
│  (Livewire + Blade + Tailwind CSS)   │
└─────────────────┬────────────────────┘
                  │
    ┌─────────────▼──────────────┐
    │   Laravel HTTP Сервер      │
    │  (Port 8000/80 по умолч.)   │
    │  + Livewire Компоненты      │
    └─────────────┬──────────────┘
                  │
    ┌─────────────▼─────────────────┐
    │    PHP Приложение             │
    │  - App Logic                  │
    │  - Routing                    │
    │  - Authentication (Sanctum)   │
    │  - Services (Pomodoro, Todo)  │
    └─────────────┬─────────────────┘
                  │
    ┌─────────────▼──────────────────┐
    │   Очереди задач (Queue)        │
    │   (Database Connection)        │
    └─────────────┬──────────────────┘
                  │
    ┌─────────────▼──────────────────┐
    │        База данных             │
    │  SQLite / PostgreSQL / MySQL   │
    │  - Tasks, Tags, Sessions       │
    │  - Users, Categories, Statuses │
    │  - Calendar Events             │
    └────────────────────────────────┘
```

### 2.2 Основные компоненты

| Компонент | Назначение | Технология |
|-----------|-----------|-----------|
| **Frontend** | Интерфейс пользователя | Blade + Livewire 3.0 + Tailwind CSS 4.1 |
| **Backend** | Обработка логики и данных | Laravel 11.0 + PHP 8.2+ |
| **Real-time** | Живое обновление компонентов | Livewire (reactive components) |
| **БД** | Хранение данных | SQLite (по умолч.), поддержка MySQL/PostgreSQL |
| **Очереди** | Асинхронные задачи | Database Queue Driver |
| **Аутентификация** | Управление пользователями | Laravel Sanctum 4.0 |
| **Фронтенд-сборка** | Обработка CSS/JS | Vite 7.0 + Tailwind CSS |

---

## 3. Технологический стек

### 3.1 Требуемое окружение

| Компонент | Версия | Назначение |
|-----------|--------|-----------|
| PHP | 8.2+ | Язык программирования |
| Node.js | 16.0+ | npm для управления зависимостями |
| Composer | 2.5+ | PHP package manager |
| npm / yarn | Последняя | JavaScript package manager |
| ОС | Linux, macOS, Windows | Сервер приложения |

### 3.2 Основные зависимости

**Backend (PHP):**
- `laravel/framework` (11.0) — основной фреймворк
- `laravel/livewire` (3.0) — reactive компоненты
- `laravel/sanctum` (4.0) — API аутентификация
- `laravel/horizon` (5.0) — мониторинг очередей
- `guzzlehttp/guzzle` (7.8) — HTTP-клиент

**Frontend (Node.js):**
- `tailwindcss` (4.1.17) — CSS фреймворк
- `vite` (7.0.7) — сборщик модулей
- `laravel-vite-plugin` (2.0) — интеграция с Laravel
- `axios` (1.11) — HTTP-клиент для JS

---

## 4. Структура проекта

```
tomodoro/
├── app/                          # Основной код приложения
│   ├── Http/                     # HTTP слой
│   │   ├── Controllers/          # Контроллеры маршрутов
│   │   ├── Livewire/            # Livewire компоненты
│   │   ├── Requests/            # Form Request классы (валидация)
│   │   └── Resources/           # Resource классы (трансформация данных)
│   ├── Livewire/                # Основные Livewire компоненты
│   │   ├── CalendarOverview.php # Календарь событий
│   │   ├── PomodoroTimer.php    # Таймер Pomodoro
│   │   ├── PresetManager.php    # Управление пресетами
│   │   ├── TodoManager.php      # Управление задачами
│   │   └── ...
│   ├── Models/                  # Eloquent модели
│   │   ├── User.php             # Модель пользователя
│   │   ├── Task.php             # Модель задачи
│   │   ├── PomodoroSession.php  # Сессия Pomodoro
│   │   ├── Tag.php              # Тег задачи
│   │   ├── TaskCategory.php     # Категория задачи
│   │   ├── TaskStatus.php       # Статус задачи
│   │   ├── CalendarEvent.php    # Событие календаря
│   │   └── ...
│   ├── Services/                # Бизнес-логика
│   │   ├── Pomodoro/            # Сервис управления Pomodoro
│   │   ├── Todo/                # Сервис управления задачами
│   │   ├── Calendar/            # Сервис календаря
│   │   ├── Export/              # Экспорт данных
│   │   ├── Import/              # Импорт данных
│   │   └── ...
│   └── Providers/               # Service Providers
│
├── config/                      # Конфигурационные файлы
│   ├── app.php                  # Основные параметры приложения
│   ├── database.php             # Подключение к БД
│   ├── auth.php                 # Параметры аутентификации
│   ├── mail.php                 # Параметры отправки писем
│   └── ...
│
├── database/                    # Миграции и сидеры
│   ├── migrations/              # Schema миграции
│   │   ├── *_create_users_table.php
│   │   ├── *_create_tasks_table.php
│   │   ├── *_create_tags_table.php
│   │   ├── *_create_pomodoro_sessions_table.php
│   │   ├── *_create_calendar_events_table.php
│   │   ├── *_create_task_categories_table.php
│   │   ├── *_create_task_statuses_table.php
│   │   └── ...
│   ├── factories/               # Factory классы для тестирования
│   └── seeders/                 # Database seeders
│
├── resources/                   # Frontend ресурсы
│   ├── css/                     # CSS файлы (Tailwind)
│   ├── js/                      # JavaScript файлы
│   └── views/                   # Blade шаблоны
│
├── routes/                      # Маршруты приложения
│   ├── web.php                  # Веб-маршруты (Blade)
│   ├── api.php                  # API маршруты (JSON)
│   ├── console.php              # Консольные команды
│   └── ...
│
├── public/                      # Публичная папка вебсервера
│   ├── index.php                # Точка входа
│   ├── build/                   # Собранные Vite ресурсы
│   └── ...
│
├── storage/                     # Хранилище данных приложения
│   ├── app/                     # Загруженные файлы
│   ├── framework/               # Framework кэш
│   └── logs/                    # Логи приложения
│
├── tests/                       # Тесты
│   ├── Feature/                 # Feature тесты
│   └── Unit/                    # Unit тесты
│
├── bootstrap/                   # Bootstrap файлы
├── vendor/                      # Composer зависимости
├── node_modules/                # npm зависимости
│
├── .env.example                 # Пример переменных окружения
├── .env                         # Переменные окружения (локально)
├── composer.json                # Зависимости PHP
├── package.json                 # Зависимости Node.js
├── vite.config.js               # Конфигурация Vite
├── tailwind.config.js           # Конфигурация Tailwind CSS
├── phpunit.xml                  # Конфигурация тестов
├── artisan                      # Laravel CLI утилита
└── README.md                    # Документация
```

---

## 5. Основные модели данных

### 5.1 Диаграмма связей

```
User (1) ──┬──→ (N) Task
           ├──→ (N) PomodoroSession
           ├──→ (N) CalendarEvent
           └──→ (N) Preferences (Theme, Settings)

Task (1) ──┬──→ (N) Tag (Many-to-Many)
           ├──→ (1) TaskCategory
           ├──→ (1) TaskStatus
           └──→ (N) PomodoroSession

PomodoroSession (N) ──→ (1) Task
```

### 5.2 Ключевые таблицы

| Таблица | Назначение |
|---------|-----------|
| `users` | Данные пользователей |
| `tasks` | Задачи пользователей |
| `tags` | Теги для категоризации |
| `task_tag` | Связь много-ко-многим |
| `pomodoro_sessions` | Сессии работы по Pomodoro |
| `calendar_events` | События в календаре |
| `task_categories` | Категории задач |
| `task_statuses` | Статусы задач |

---

## 6. Поток данных при сессии Pomodoro

```
1. Пользователь выбирает задачу и нажимает "Начать сессию"
   ↓
2. Frontend отправляет запрос (AJAX/Livewire) к backend
   ↓
3. PomodoroService создаёт запись PomodoroSession в БД
   ↓
4. Livewire компонент начинает реактивно отчитываться о состоянии
   ↓
5. Таймер отсчитывает 25 минут (или настройка)
   ↓
6. По завершении PomodoroService обновляет сессию (завершена)
   ↓
7. Система может создать уведомление/звук
   ↓
8. Данные сохраняются в БД для аналитики
```

---

## 7. Значимые особенности

- **Реактивные компоненты:** Livewire обеспечивает real-time обновление без перезагрузки страницы
- **Многоуровневая валидация:** На уровне Form Requests и моделей
- **Асинхронная обработка:** Использование очередей для длительных операций
- **Respnsive дизайн:** Tailwind CSS для адаптивности под все устройства
- **API:** Поддержка RESTful API через Laravel Sanctum для мобильных клиентов

---

## 8. Переменные окружения (ключевые)

| Переменная | Описание | Пример |
|------------|---------|---------|
| `APP_NAME` | Название приложения | Tomodoro |
| `APP_ENV` | Окружение | local, production |
| `APP_KEY` | Ключ шифрования | base64:... (генерируется) |
| `APP_URL` | URL приложения | http://localhost |
| `DB_CONNECTION` | Тип БД | sqlite, mysql, pgsql |
| `MAIL_MAILER` | Сервис отправки писем | smtp, log |
| `QUEUE_CONNECTION` | Драйвер очередей | database, redis |
| `SESSION_DRIVER` | Хранилище сессий | database, cookie |

---

## 9. Расширяемость

Приложение разработано с учётом SOLID принципов и позволяет:

1. **Добавлять новые Service классы** в `app/Services/`
2. **Расширять Livewire компоненты** в `app/Livewire/`
3. **Создавать новые модели** в `app/Models/`
4. **Подключать новые пакеты** через Composer/npm
5. **Кастомизировать стили** через Tailwind конфигурацию

---

**Документ соответствует ГОСТ 19.105-78 "Общие требования к программным документам"**
