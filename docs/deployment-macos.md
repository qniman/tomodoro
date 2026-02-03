# Развёртка на macOS

## Установка зависимостей

1. Установите Homebrew: `/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"`
2. Установите PHP, Composer и Node.js:
```
brew install php composer node
```

## Варианты развёртки
- Разработка: используйте Valet, Laravel Sail или Docker.
- Продакшн: рекомендуется Docker или настройка Nginx + PHP-FPM.

## Планировщик и очереди

- Scheduler: используйте `cron` или `launchd` для запуска `php artisan schedule:run`.
- Queue: запустите `php artisan queue:work` как сервис (launchd) или через supervisor в Docker.

## SSL

- Для локальной разработки используйте Valet (автоматически настроит HTTPS).
- Для продакшна используйте Certbot/Nginx для получения SSL-сертификатов.

---

Если хотите — подготовлю пример `launchd`-plist для очереди и планировщика.