# Руководство системного администратора

Этот документ предназначен для развертки, управления и сопровождения Tomodoro в продакшн-среде.

## Требования

- PHP 8.1+
- Composer
- Node.js 18+
- База данных: MySQL 8 / PostgreSQL
- Веб-сервер: Nginx или Apache
- Redis (рекомендуется для очередей/кеша)

## Переменные окружения (основные)

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_DRIVER`, `QUEUE_CONNECTION`, `SESSION_DRIVER`
- `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`
- `MAIL_*` — настройки почты

## Миграции и обновления

1. Подключитесь к серверу и перейдите в папку приложения.
2. Сделайте backup базы данных до миграций.
3. Выполните:
```
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Очереди и планировщик

- Очереди: используйте `supervisor` или `systemd` для запуска `php artisan queue:work --sleep=3 --tries=3`.
- Планировщик: добавьте запись в cron: `* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`.

## Файловые разрешения

- Убедитесь, что `storage/` и `bootstrap/cache/` доступны для записи веб-сервером:
```
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Резервное копирование

- Регулярно делайте бэкапы базы данных и директории `storage/`.
- Проверяйте целостность резервных копий и процесс восстановления.

## Логи и мониторинг

- Логи Laravel: `storage/logs/laravel.log`.
- Настройте централизованный сбор логов и мониторинг состояния очередей/сервисов.

## Рекомендации по безопасности

- Всегда используйте HTTPS (Let's Encrypt/Certbot).
- Ограничьте доступ к административным интерфейсам и используйте двухфакторную аутентификацию где возможно.

---

Если хотите, добавлю готовые конфигурации `nginx`/`systemd`/`supervisor` и примеры бэкап-скриптов.