# 📋 Инструкция по запуску install.sh v3.1

## Что было исправлено в v3.1

### Ошибка bootstrap/cache
```
The /home/qsi/tomodoro/bootstrap/cache directory must be present and writable.
Script @php artisan package:discover --ansi handling the post-autoload-dump 
event returned with error code 1
```

**Решение**: Все требуемые Laravel директории создаются **ДО** запуска composer install.

---

## Запуск на Linux/macOS

### Быстрая установка (одна команда)

```bash
git clone https://github.com/qniman/tomodoro
cd tomodoro
chmod +x install.sh
./install.sh
```

### Или пошагово

```bash
# 1. Клонируем репозиторий
git clone https://github.com/qniman/tomodoro
cd tomodoro

# 2. Делаем скрипт исполняемым
chmod +x install.sh

# 3. Запускаем установку
./install.sh

# Готово! Сервер запустится на http://localhost:8000
```

---

## Что делает install.sh v3.1

### 1️⃣ Проверка ОС и инструментов
- ✅ Определяет Linux дистрибутив (Debian, CentOS, macOS)
- ✅ Проверяет установлены ли: PHP, Node.js, npm, Composer

### 2️⃣ Установка PHP
```
Если PHP не установлен:
  • Ubuntu/Debian: sudo apt-get install php8.3-cli php8.3-fpm ...
  • CentOS/RHEL: sudo yum install php-cli php-fpm ...
  • macOS: brew install php
```

### 3️⃣ Установка PHP расширений
```
Проверяет наличие:
  • ext-xml
  • ext-dom
  • ext-curl
  • ext-mbstring
  • ext-zip
  • ext-intl
  
Если отсутствуют - устанавливает автоматически
```

### 4️⃣ Установка Node.js 20 LTS
```
Если Node.js не установлен или версия < 20:
  • Добавляет репозиторий NodeSource
  • Устанавливает Node.js 20 LTS
  • npm 10+ устанавливается автоматически
```

### 5️⃣ Установка Composer
```
Если Composer не установлен:
  • Загружает installer с getcomposer.org
  • Перемещает в /usr/local/bin/composer
  • Делает исполняемым
```

### 6️⃣ Создание Laravel директорий ⭐ НОВОЕ в v3.1
```bash
mkdir -p bootstrap/cache              # Кэш bootstrap
mkdir -p storage/app                  # Хранилище файлов
mkdir -p storage/logs                 # Логи приложения
mkdir -p storage/framework/cache      # Кэш фреймворка
mkdir -p storage/framework/sessions   # Сессии
mkdir -p storage/framework/views      # Скомпилированные views
mkdir -p database                     # БД
chmod -R 755 bootstrap storage database
```

### 7️⃣ Установка зависимостей
```
composer install --no-interaction
npm install
```

### 8️⃣ Создание конфигурации
```
Создает .env если не существует:
  • APP_NAME=Tomodoro
  • APP_ENV=local (или из .env.example)
  • DB_CONNECTION=sqlite
  • DB_DATABASE=database/database.sqlite
```

### 9️⃣ Инициализация приложения
```
php artisan key:generate --force      # Генерирует ключ
php artisan migrate --force           # Запускает миграции
npm run build                         # Собирает фронтенд
```

### 🔟 Запуск сервера
```
php artisan serve

Доступно на: http://localhost:8000
```

---

## Проверка успешной установки

После запуска скрипта проверьте:

```bash
# 1. Все ли директории созданы?
ls -la bootstrap/cache
ls -la storage/app
ls -la database/

# 2. Все ли установлено?
php -v              # PHP 8.3+
php -m | grep xml   # Расширения
node -v             # Node 20+
npm -v              # npm 10+
composer -v         # Composer 2.6+

# 3. Приложение работает?
curl http://localhost:8000

# 4. Логи чистые?
cat storage/logs/laravel.log | tail -20
```

---

## Если что-то пошло не так

### Логи находятся в `/tmp/`

```bash
# Логи composer
cat /tmp/composer.log

# Логи npm
cat /tmp/npm.log

# Логи npm build
cat /tmp/npm-build.log

# Логи artisan
cat /tmp/artisan-key.log
cat /tmp/artisan-migrate.log
```

### Типичные проблемы

#### 1. Permission denied при chmod
```bash
# Если не хватает прав:
sudo chmod -R 755 bootstrap storage database
```

#### 2. Permission denied на storage/logs
```bash
# Если приложение не может писать логи:
sudo chown -R $USER:$USER storage bootstrap
sudo chmod -R 755 storage bootstrap
```

#### 3. Composer требует памяти
```bash
# Если Composer падает с out of memory:
php -d memory_limit=512M composer install
```

#### 4. Node.js версия не обновилась
```bash
# Проверьте что установилась правильная версия:
node -v  # Должно быть v20.x.x

# Если нет, обновите вручную:
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

---

## Требуемые права доступа

После успешной установки права должны быть:

```
bootstrap/
  cache/                   755 (drwxr-xr-x)

storage/
  app/                     755 (drwxr-xr-x)
  logs/                    755 (drwxr-xr-x)
  framework/
    cache/                 755 (drwxr-xr-x)
    sessions/              755 (drwxr-xr-x)
    views/                 755 (drwxr-xr-x)

database/
  database.sqlite          644 (-rw-r--r--)
```

---

## Production режим

Если после установки хотите перейти в production:

```bash
# 1. Обновите .env
nano .env
# Измените:
#   APP_ENV=local  →  APP_ENV=production
#   APP_DEBUG=true →  APP_DEBUG=false

# 2. Оптимизируйте конфигурацию
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Используйте web server вместо artisan serve
# Apache, Nginx, или PHP-FPM
```

---

## Поддерживаемые платформы

### Linux
- Ubuntu 20.04+
- Ubuntu 22.04+
- Ubuntu 24.04+
- Debian 11+
- Debian 12+
- CentOS 7+
- CentOS 8+
- CentOS Stream 9
- RHEL 7+
- RHEL 8+
- RHEL 9+

### macOS
- macOS 10.15+
- macOS 11+
- macOS 12+
- macOS 13+
- macOS 14+

### Windows
Используйте install.bat (двойной клик)

---

**Версия**: 3.1  
**Последнее обновление**: 29 января 2024 г.  
**Статус**: ✅ Production Ready
