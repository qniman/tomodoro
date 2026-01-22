# Tomodoro - Руководство по установке и развёртыванию

**Версия документа:** 1.0  
**Статус:** Утверждено  
**Дата:** 22.01.2026

---

## Оглавление
1. [Требования к системе](#1-требования-к-системе)
2. [Предварительная подготовка](#2-предварительная-подготовка)
3. [Установка на локальной машине (для разработчиков)](#3-установка-на-локальной-машине)
4. [Развёртывание на продакшене](#4-развёртывание-на-продакшене)
5. [Проверка установки](#5-проверка-установки)
6. [Структура конфигурационных файлов](#6-структура-конфигурационных-файлов)
7. [Процесс обновления](#7-процесс-обновления)
8. [Диагностика и решение проблем](#8-диагностика-и-решение-проблем)

---

## 1. Требования к системе

### 1.1 Системные требования

| Компонент | Минимальная версия | Рекомендуемая версия |
|-----------|-------------------|-------------------|
| PHP | 8.2.0 | 8.3.x |
| Node.js | 16.x LTS | 20.x LTS |
| npm | 8.0+ | 10.0+ |
| Composer | 2.5+ | 2.7+ |
| ОС | Windows 10, macOS 10.15, Ubuntu 20.04 | Windows 11, macOS 12+, Ubuntu 22.04+ |
| RAM | 2 GB | 4 GB |
| Дисковое пространство | 500 MB | 2 GB |

### 1.2 Поддерживаемые базы данных

- **SQLite 3.8+** (по умолчанию, для разработки)
- **MySQL 8.0+** (для production)
- **PostgreSQL 12+** (для production)

### 1.3 Веб-серверы

- **Apache 2.4+** (с модулем mod_rewrite)
- **Nginx 1.14+** (рекомендуется)
- **PHP встроенный сервер** (только для разработки)

### 1.4 Браузеры (для пользователей)

- Google Chrome 90+
- Mozilla Firefox 88+
- Safari 14+
- Edge 90+

---

## 2. Предварительная подготовка

### 2.1 Установка PHP

**Windows:**
```powershell
# Установка через Chocolatey
choco install php

# Проверка версии
php -v
```

**macOS:**
```bash
# Установка через Homebrew
brew install php@8.3

# Проверка версии
php -v
```

**Linux (Ubuntu/Debian):**
```bash
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-common php8.3-curl php8.3-mbstring php8.3-mysql php8.3-sqlite3 php8.3-xml php8.3-bcmath php8.3-zip

# Проверка версии
php -v
```

### 2.2 Установка Node.js и npm

**Windows/macOS:**
- Скачайте с https://nodejs.org (LTS версию)
- Запустите установщик и следуйте инструкциям

**Linux (Ubuntu/Debian):**
```bash
curl -sL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# Проверка версии
node -v
npm -v
```

### 2.3 Установка Composer

**Windows:**
- Скачайте установщик с https://getcomposer.org/Composer-Setup.exe
- Запустите и следуйте инструкциям

**macOS/Linux:**
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Проверка версии
composer --version
```

### 2.4 Клонирование репозитория

```bash
# Клонируйте репозиторий
git clone https://github.com/username/tomodoro.git

# Перейдите в папку проекта
cd tomodoro
```

Если репозитория нет в Git, распакуйте архив в выбранную папку.

---

## 3. Установка на локальной машине

### 3.1 Быстрая установка (рекомендуемый способ)

```bash
# Перейдите в папку проекта
cd tomodoro

# Выполните скрипт setup (он сделает всё автоматически)
composer setup
```

**Этот скрипт выполняет:**
1. `composer install` — установка PHP-зависимостей
2. Создание файла `.env` из `.env.example`
3. `php artisan key:generate` — генерация APP_KEY
4. `php artisan migrate --force` — создание таблиц БД
5. `npm install` — установка Node.js-зависимостей
6. `npm run build` — сборка фронтенда (Tailwind CSS, Vite)

### 3.2 Пошаговая установка (для опытных пользователей)

#### Шаг 1: Установка PHP-зависимостей
```bash
composer install
```

#### Шаг 2: Создание файла окружения
```bash
# Скопируйте шаблон
cp .env.example .env

# Откройте .env в редакторе и настройте необходимые параметры
# (см. раздел 6 "Структура конфигурационных файлов")
```

#### Шаг 3: Генерация ключа приложения
```bash
php artisan key:generate

# Проверка: в файле .env должна появиться строка
# APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

#### Шаг 4: Создание структуры БД
```bash
# Для SQLite (по умолчанию)
php artisan migrate

# ИЛИ для MySQL/PostgreSQL (после конфигурации в .env)
php artisan migrate --seed
```

#### Шаг 5: Установка Node.js-зависимостей
```bash
npm install
```

#### Шаг 6: Сборка фронтенда
```bash
# Для разработки (с hot reload)
npm run dev

# ИЛИ для production (оптимизированная сборка)
npm run build
```

### 3.3 Запуск приложения локально

**Способ 1: PHP встроенный сервер (простой)**
```bash
php artisan serve
# Приложение будет доступно по адресу: http://localhost:8000
```

**Способ 2: Полная среда разработки (рекомендуется)**
```bash
# В одном терминале (в папке проекта):
composer dev

# Это запустит одновременно:
# - PHP сервер на порту 8000
# - Laravel Queue Listener (для асинхронных задач)
# - Vite dev сервер (для hot module replacement CSS/JS)
```

**Способ 3: Через Docker (если установлен)**
```bash
# Используя Laravel Sail
./vendor/bin/sail up -d

# Создание БД и миграции
./vendor/bin/sail artisan migrate --seed
```

---

## 4. Развёртывание на продакшене

### 4.1 Подготовка сервера

#### 4.1.1 Установка на Ubuntu/Debian

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Установка зависимостей
sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-zip git

# Установка Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Проверка версий
php -v
node -v
npm -v
```

#### 4.1.2 Установка на CentOS/RHEL

```bash
sudo yum update -y
sudo yum install -y epel-release
sudo yum install -y nginx php-fpm php-mysql php-curl php-mbstring php-xml php-bcmath php-zip git

# Node.js
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install -y nodejs

# Проверка версий
php -v
node -v
npm -v
```

### 4.2 Клонирование и установка приложения

```bash
# Перейдите в папку веб-сервера
cd /var/www

# Клонируйте репозиторий (или загрузите архив)
sudo git clone https://github.com/username/tomodoro.git tomodoro

# Установите права доступа
sudo chown -R www-data:www-data /var/www/tomodoro
sudo chmod -R 755 /var/www/tomodoro
sudo chmod -R 777 /var/www/tomodoro/storage
sudo chmod -R 777 /var/www/tomodoro/bootstrap/cache

# Установите зависимости
cd /var/www/tomodoro
composer install --no-dev --optimize-autoloader
npm install --production
npm run build

# Создайте и конфигурируйте .env файл
sudo cp .env.example .env
sudo nano .env  # или используйте другой редактор

# Установите правильный ключ
php artisan key:generate

# Мигрируйте БД
php artisan migrate --force
```

### 4.3 Конфигурация Nginx

Создайте файл `/etc/nginx/sites-available/tomodoro`:

```nginx
server {
    listen 80;
    listen [::]:80;
    
    server_name tomodoro.example.com;
    
    root /var/www/tomodoro/public;
    index index.php;

    # Перенаправление с http на https (если включен SSL)
    # return 301 https://$server_name$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Кэширование статических файлов
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Размер загружаемого файла
    client_max_body_size 20M;

    error_log /var/log/nginx/tomodoro_error.log;
    access_log /var/log/nginx/tomodoro_access.log;
}
```

Активируйте конфигурацию:

```bash
sudo ln -s /etc/nginx/sites-available/tomodoro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4.4 Конфигурация Apache

Если используется Apache, создайте файл `.htaccess` в папке `/var/www/tomodoro/public`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 4.5 Настройка очередей (Queue)

Для асинхронной обработки задач, добавьте в crontab:

```bash
sudo crontab -e

# Добавьте строку:
* * * * * php /var/www/tomodoro/artisan schedule:run >> /dev/null 2>&1

# И запустите очередь (как постоянный процесс):
# Используйте supervisor или создайте systemd сервис
```

**Systemd сервис для очереди** (`/etc/systemd/system/tomodoro-queue.service`):

```ini
[Unit]
Description=Tomodoro Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/tomodoro
ExecStart=/usr/bin/php artisan queue:listen
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Активируйте:
```bash
sudo systemctl daemon-reload
sudo systemctl enable tomodoro-queue
sudo systemctl start tomodoro-queue
```

### 4.6 SSL сертификат (Let's Encrypt)

```bash
# Установка Certbot
sudo apt install certbot python3-certbot-nginx

# Получение сертификата
sudo certbot certonly --nginx -d tomodoro.example.com

# Обновление конфигурации Nginx (Certbot сделает это автоматически)
sudo certbot install --nginx -d tomodoro.example.com

# Проверка автоматического обновления
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

---

## 5. Проверка установки

### 5.1 Проверка компонентов

```bash
# Проверка PHP
php -v
php -m  # должен содержать: curl, mbstring, sqlite3, xml, bcmath, zip

# Проверка npm
npm -v

# Проверка composer
composer --version

# Проверка Node.js
node -v
```

### 5.2 Проверка приложения

```bash
# Перейдите в папку проекта
cd /path/to/tomodoro

# Запустите тесты
php artisan test

# Проверка artisan команд
php artisan list

# Проверка миграций
php artisan migrate:status

# Проверка кэша конфига
php artisan config:cache
php artisan route:cache
```

### 5.3 Проверка в браузере

После запуска приложения откройте в браузере:
- Локально: `http://localhost:8000`
- На сервере: `http://your-domain.com`

**Признаки успешной установки:**
- Загрузилась главная страница
- Стили CSS применены (используется Tailwind)
- JavaScript интерактивные элементы работают
- Страницы загружаются без ошибок 404

---

## 6. Структура конфигурационных файлов

### 6.1 Файл .env

**Основные параметры приложения:**

```env
APP_NAME=Tomodoro
APP_ENV=production              # или local для разработки
APP_DEBUG=false                 # false на production
APP_URL=https://tomodoro.example.com

APP_LOCALE=ru                   # Язык интерфейса
APP_TIMEZONE=Europe/Moscow      # Часовой пояс

# Шифрование
APP_KEY=base64:xxx...          # Генерируется автоматически
```

**Конфигурация БД (выберите одну):**

```env
# SQLite (для разработки)
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# PostgreSQL (для production)
DB_CONNECTION=pgsql
DB_HOST=db.example.com
DB_PORT=5432
DB_DATABASE=tomodoro_db
DB_USERNAME=tomodoro_user
DB_PASSWORD=SecurePassword123

# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tomodoro_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Параметры сессии и кэша:**

```env
SESSION_DRIVER=database         # Хранение сессий в БД
SESSION_LIFETIME=120            # Время жизни в минутах
SESSION_ENCRYPT=false

CACHE_STORE=database            # Кэширование в БД (или redis)
QUEUE_CONNECTION=database       # Очереди в БД (или redis)
```

**Email (для отправки уведомлений):**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io      # или другой SMTP сервис
MAIL_PORT=465
MAIL_USERNAME=your@example.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tomodoro.com
MAIL_FROM_NAME=Tomodoro
```

### 6.2 Файл config/app.php

Основные настройки:
```php
'name' => env('APP_NAME', 'Tomodoro'),
'env' => env('APP_ENV', 'production'),
'debug' => env('APP_DEBUG', false),
'url' => env('APP_URL', 'http://localhost'),
'timezone' => env('APP_TIMEZONE', 'UTC'),
'locale' => env('APP_LOCALE', 'en'),
```

### 6.3 Файл config/database.php

Выбор подключения БД (автоматически по `DB_CONNECTION`).

---

## 7. Процесс обновления

### 7.1 Обновление на новую версию

```bash
# Перейдите в папку проекта
cd /var/www/tomodoro

# Сделайте резервную копию БД
php artisan backup:run

# Получите последние изменения
git pull origin main

# Или загрузите новую версию и распакуйте

# Обновите зависимости
composer install --no-dev --optimize-autoloader
npm install --production

# Выполните миграции БД (если они добавлены)
php artisan migrate --force

# Пересоберите фронтенд
npm run build

# Очистите кэш
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Перезагрузите сервер
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### 7.2 Откат при проблемах

```bash
# Восстановите из резервной копии
php artisan backup:restore

# Откатите миграции (осторожно!)
php artisan migrate:rollback

# Перезагрузите приложение
sudo systemctl restart tomodoro-queue
sudo systemctl restart nginx
```

---

## 8. Диагностика и решение проблем

### 8.1 Проблема: "Class not found" или ошибки autoloader

```bash
# Решение: переиндексируйте composer
composer dump-autoload
php artisan cache:clear
```

### 8.2 Проблема: "SQLSTATE[HY000]: General error"

```bash
# Решение: проверьте прав доступа на storage
chmod -R 777 storage bootstrap/cache

# Или переостановите БД
php artisan migrate:fresh --seed
```

### 8.3 Проблема: CSS/JS не загружаются

```bash
# Решение: пересоберите фронтенд
npm run build

# Или запустите dev режим
npm run dev
```

### 8.4 Проблема: Очереди не работают

```bash
# Проверьте статус сервиса очереди
sudo systemctl status tomodoro-queue

# Перезагрузите очередь
sudo systemctl restart tomodoro-queue

# Или запустите вручную
php artisan queue:listen
```

### 8.5 Проверка логов

```bash
# Основной лог
tail -f storage/logs/laravel.log

# Лог web сервера (Nginx)
tail -f /var/log/nginx/tomodoro_error.log
tail -f /var/log/nginx/tomodoro_access.log

# Лог PHP-FPM
tail -f /var/log/php8.3-fpm.log
```

---

## 9. Поддерживаемые команды

### 9.1 Основные команды управления

```bash
# Информация о приложении
php artisan about

# Миграции БД
php artisan migrate              # Применить миграции
php artisan migrate:refresh      # Переделать все миграции
php artisan migrate:rollback     # Откатить последние миграции

# Кэширование
php artisan config:cache        # Кэшировать конфиг
php artisan route:cache         # Кэшировать маршруты
php artisan cache:clear         # Очистить кэш

# Артисан tinker (REPL)
php artisan tinker              # Интерактивная консоль

# Тестирование
php artisan test                # Запустить все тесты
php artisan test --filter=NameOfTest  # Запустить конкретный тест
```

---

**Документ соответствует ГОСТ 19.502-78 "Руководство пользователя"**

Дата последнего обновления: 22.01.2026
