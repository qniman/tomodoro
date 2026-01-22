# Tomodoro - Системные требования и конфигурация

**Версия документа:** 1.0  
**Статус:** Утверждено  
**Дата:** 22.01.2026

---

## 1. Системные требования для конечного пользователя

### 1.1 Аппаратные требования

| Параметр | Минимум | Рекомендуемо |
|----------|---------|-------------|
| **Оперативная память (RAM)** | 512 MB | 2 GB |
| **Место на диске** | Не требуется | Не требуется |
| **Интернет** | 256 kbps (2G) | 1 Mbps (4G/LTE) |
| **Процессор** | Любой современный | i3/Ryzen 3 или выше |

### 1.2 Ограничения браузера

| Браузер | Минимальная версия | Примечания |
|---------|-------------------|-----------|
| **Google Chrome** | 90+ | Оптимальная поддержка |
| **Mozilla Firefox** | 88+ | Полная поддержка |
| **Apple Safari** | 14+ | iOS 14+, macOS 11+ |
| **Microsoft Edge** | 90+ | Chromium-based |
| **Opera** | 76+ | Полная поддержка |

### 1.3 Сетевые требования

- **Интернет соединение**: Постоянное соединение (WiFi или мобильная сеть)
- **Пинг**: < 200 ms (для оптимальной работы)
- **Пропускная способность**: 50-100 kbps в момент использования
- **HTTPS**: Обязателен на production сервере

### 1.4 Операционные системы

| ОС | Версия | Поддержка |
|----|--------|----------|
| **Windows** | 7 SP1+ | ✓ Полная |
| **macOS** | 10.13+ | ✓ Полная |
| **Linux** | Ubuntu 16.04+, CentOS 7+ | ✓ Полная |
| **iOS** | 12+ | ✓ Веб-приложение |
| **Android** | 6.0+ | ✓ Веб-приложение |
| **ChromeOS** | Все версии | ✓ Полная |

---

## 2. Системные требования для администратора/разработчика

### 2.1 Серверная архитектура

```
┌─────────────────────────────────────────────────┐
│           Веб-браузер (Клиент)                  │
│     Windows / macOS / Linux / iOS / Android     │
└────────────────────┬────────────────────────────┘
                     │
                HTTPS/HTTP
                     │
        ┌────────────▼────────────┐
        │  Веб-сервер (Nginx/Apache)    │
        │       + PHP-FPM         │
        └────────────┬────────────┘
                     │
        ┌────────────▼────────────┐
        │  Laravel Application    │
        │  + Livewire Components  │
        └────────────┬────────────┘
                     │
        ┌────────────▼────────────┐
        │   База Данных           │
        │ SQLite/MySQL/PostgreSQL │
        └────────────────────────┘
```

### 2.2 Требования для development-окружения

#### 2.2.1 Язык и фреймворки

| Компонент | Версия | Назначение |
|-----------|--------|-----------|
| **PHP** | 8.2+ | Язык программирования backend |
| **Laravel** | 11.0+ | Основной фреймворк |
| **Livewire** | 3.0+ | Reactive компоненты |
| **Blade** | 11.0+ | Шаблонизатор (встроен в Laravel) |
| **Tailwind CSS** | 4.1+ | CSS фреймворк |
| **Vite** | 7.0+ | Сборщик модулей frontend |

#### 2.2.2 Node.js инструментарий

| Инструмент | Версия | Назначение |
|-----------|--------|-----------|
| **Node.js** | 16+ LTS | JavaScript runtime |
| **npm** | 8.0+ | Package manager для JS |
| **yarn** | 3.0+ | Альтернативный PM (опционально) |

#### 2.2.3 Менеджеры пакетов

| Менеджер | Версия | Язык | Файл |
|----------|--------|------|------|
| **Composer** | 2.5+ | PHP | `composer.json` |
| **npm** | 8.0+ | JavaScript | `package.json` |

#### 2.2.4 Базы данных (для development)

| СУБД | Версия | Размер | Назначение |
|------|--------|--------|-----------|
| **SQLite** | 3.8+ | ~100 MB | По умолчанию (локальная) |
| **MySQL** | 8.0+ | Настраивается | Альтернатива |
| **PostgreSQL** | 12+ | Настраивается | Рекомендуется для production |

### 2.3 Требования для production-окружения

#### 2.3.1 Операционная система сервера

| ОС | Версия | Рекомендация |
|----|--------|-------------|
| **Ubuntu / Debian** | 20.04 LTS+ | ✅ Рекомендуется |
| **CentOS / RHEL** | 8+ | ✅ Рекомендуется |
| **Alpine Linux** | 3.15+ | ✓ Поддерживается (Docker) |
| **Windows Server** | 2016+ | ✓ Поддерживается (нежелательно) |

#### 2.3.2 Серверная конфигурация

| Компонент | Специфика | Примечания |
|-----------|-----------|-----------|
| **Веб-сервер** | Nginx 1.14+ или Apache 2.4+ | Nginx рекомендуется |
| **PHP** | 8.2+ с FPM | Обязателен FPM для Nginx |
| **БД** | PostgreSQL 12+ или MySQL 8.0+ | SQLite для production нежелателен |
| **Кэш** | Redis 6.0+ | Опционально (улучшает производительность) |
| **Очереди** | Supervisor или systemd | Для фонового обработки |

#### 2.3.3 Аппаратные требования сервера

| Параметр | Малое приложение | Среднее приложение | Высоконагруженное |
|----------|-----------------|------------------|-----------------|
| **CPU** | 2 ядра | 4 ядра | 8+ ядер |
| **RAM** | 2 GB | 4 GB | 8+ GB |
| **Хранилище** | 20 GB | 50 GB | 100+ GB |
| **Пропускная способность** | 10 Mbps | 25 Mbps | 100+ Mbps |
| **Пользователей одновременно** | 50 | 500 | 5000+ |

---

## 3. Переменные окружения (.env)

### 3.1 Полный список переменных

#### Основные параметры приложения

```env
# Название приложения и версия
APP_NAME=Tomodoro
APP_VERSION=1.0

# Окружение: local, development, production
APP_ENV=local

# Режим отладки: true для development, false для production
APP_DEBUG=true

# URL приложения
APP_URL=http://localhost:8000

# Местоположение (язык и часовой пояс)
APP_LOCALE=ru
APP_TIMEZONE=Europe/Moscow

# Ключ шифрования (генерируется автоматически)
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Обслуживание приложения
APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database
```

#### Логирование

```env
# Канал логирования: single, daily, slack, syslog, errorlog
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug  # debug, info, notice, warning, error, critical, alert, emergency
```

#### База данных

```env
# Драйвер БД: sqlite, mysql, pgsql, sqlsrv
DB_CONNECTION=sqlite

# Для SQLite (используется по умолчанию)
# База хранится в database/database.sqlite

# Для MySQL
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tomodoro_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Для PostgreSQL
DB_HOST=db.example.com
DB_PORT=5432
DB_DATABASE=tomodoro_db
DB_USERNAME=tomodoro_user
DB_PASSWORD=SecurePassword123
```

#### Сессии

```env
# Драйвер сессии: file, cookie, database, memcached, redis
SESSION_DRIVER=database

# Время жизни сессии в минутах
SESSION_LIFETIME=120

# Шифрование сессии
SESSION_ENCRYPT=false

# Путь и домен для cookies
SESSION_PATH=/
SESSION_DOMAIN=null
```

#### Кэширование

```env
# Кэш хранилище: file, database, memcached, redis, dynamodb
CACHE_STORE=database

# Префикс ключей кэша
CACHE_PREFIX=tomodoro_

# Memcached параметры
MEMCACHED_HOST=127.0.0.1
MEMCACHED_PORT=11211

# Redis параметры
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Очереди задач

```env
# Очередь драйвер: sync, database, beanstalkd, sqs, redis
QUEUE_CONNECTION=database

# Количество попыток для задач
QUEUE_RETRIES=3

# Для Redis очереди
REDIS_QUEUE=default
```

#### Трансляция (Broadcasting)

```env
# Драйвер трансляции: pusher, ably, redis, log, null
BROADCAST_CONNECTION=log
BROADCAST_DRIVER=log
```

#### Файловая система

```env
# Диск по умолчанию: local, public, s3
FILESYSTEM_DISK=local

# Для AWS S3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

#### Email/SMTP

```env
# Почтовый драйвер: smtp, sendmail, mailgun, postmark, log, array
MAIL_MAILER=smtp

# SMTP параметры
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls  # tls или ssl

# От адреса и имени
MAIL_FROM_ADDRESS=noreply@tomodoro.com
MAIL_FROM_NAME="Tomodoro Приложение"
```

#### Аутентификация

```env
# Драйвер аутентификации: web, api
AUTH_DRIVER=web

# Bcrypt раунды для хеширования пароля
BCRYPT_ROUNDS=12
```

---

## 4. Конфигурационные файлы

### 4.1 Основные файлы конфигурации

| Файл | Описание |
|------|---------|
| `.env` | Переменные окружения (не версионируется) |
| `.env.example` | Пример переменных окружения (версионируется) |
| `config/app.php` | Основные параметры приложения |
| `config/database.php` | Конфигурация БД |
| `config/cache.php` | Конфигурация кэша |
| `config/queue.php` | Конфигурация очередей |
| `config/mail.php` | Конфигурация email |
| `config/session.php` | Конфигурация сессий |
| `config/auth.php` | Конфигурация аутентификации |

### 4.2 Важные параметры config/app.php

```php
'name' => env('APP_NAME', 'Tomodoro'),
'env' => env('APP_ENV', 'production'),
'debug' => env('APP_DEBUG', false),
'url' => env('APP_URL', 'http://localhost'),
'timezone' => env('APP_TIMEZONE', 'UTC'),
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => 'en',
```

### 4.3 Критические параметры для production

```env
# Обязательно на production
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# БД должна быть PostgreSQL или MySQL
DB_CONNECTION=pgsql

# Сессии в БД или Redis
SESSION_DRIVER=database

# Кэш включен
CACHE_STORE=redis

# Очереди активны
QUEUE_CONNECTION=redis

# HTTPS обязателен
HTTPS=true
```

---

## 5. Поддерживаемые интеграции

### 5.1 Внешние сервисы

| Сервис | Назначение | Версия | Статус |
|--------|-----------|--------|--------|
| **Mailgun** | Email рассылка | Последняя | ✓ Поддерживается |
| **SendGrid** | Email рассылка | Последняя | ✓ Поддерживается |
| **AWS S3** | Облачное хранилище | Последняя | ✓ Поддерживается |
| **Slack** | Интеграция уведомлений | Последняя | ✓ Поддерживается |
| **Google OAuth** | Социальная авторизация | Последняя | ✓ Поддерживается |

### 5.2 Инструменты мониторинга

| Инструмент | Назначение | Статус |
|-----------|-----------|--------|
| **Laravel Horizon** | Мониторинг очередей | ✓ Встроено |
| **Sentry** | Отслеживание ошибок | ✓ Поддерживается |
| **New Relic** | Мониторинг производительности | ✓ Поддерживается |

---

## 6. Пределы и ограничения

### 6.1 Ограничения приложения

| Параметр | Лимит | Примечания |
|----------|-------|-----------|
| **Размер файла для загрузки** | 20 MB | Настраивается в nginx/apache |
| **Максимум задач на пользователя** | Неограниченно | Зависит от памяти БД |
| **Длительность сессии** | 120 минут | Настраивается в .env |
| **Одновременных подключений** | Зависит от сервера | Обычно 1000-10000 |
| **API запросов в секунду** | Неограниченно | Можно настроить rate limiting |

### 6.2 Ограничения браузера

| Параметр | Лимит |
|----------|-------|
| **Размер localStorage** | 5-10 MB |
| **Одновременные WebSocket соединения** | 1 |
| **Размер cookie** | 4 KB |
| **Максимум фреймов в памяти** | ~50 MB |

---

## 7. Процесс проверки совместимости

### 7.1 Перед установкой

```bash
# Проверка версий
php -v            # PHP 8.2+
node -v           # Node.js 16+
npm -v            # npm 8.0+
composer --version # Composer 2.5+

# Проверка расширений PHP
php -m | grep -E 'curl|mbstring|sqlite3|xml|bcmath|zip'

# Проверка портов
netstat -tuln | grep -E '3306|5432|6379|8000'
```

### 7.2 После установки

```bash
# Проверка приложения
php artisan about

# Проверка миграций
php artisan migrate:status

# Проверка кэша
php artisan config:cache --check

# Проверка маршрутов
php artisan route:list

# Запуск тестов
php artisan test
```

---

**Документ соответствует ГОСТ 19.101-77 "Виды программ и программных документов"**

Дата последнего обновления: 22.01.2026
