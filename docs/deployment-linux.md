# Развёртка на Linux (Ubuntu/Debian)

## Установка (пример для Ubuntu)

```bash
sudo apt update
sudo apt install -y nginx mysql-server php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-intl composer unzip git
```

Установите Node.js и npm (через NodeSource или nvm).

## Конфигурация Nginx (пример /etc/nginx/sites-available/tomodoro)

```
server {
    listen 80;
    server_name example.com;
    root /var/www/tomodoro/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Настройка прав

```
sudo chown -R www-data:www-data /var/www/tomodoro
sudo chmod -R 775 storage bootstrap/cache
```

## Очереди и планировщик

- Добавьте cron: `* * * * * cd /var/www/tomodoro && php artisan schedule:run >> /dev/null 2>&1`.
- Создайте systemd-сервис для очереди: `tomodoro-queue.service` с запуском `php artisan queue:work`.

## SSL

- Используйте Certbot для получения сертификата:
```
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d example.com
```

---

Могу добавить готовые файлы `systemd` и `nginx` под ваш домен/структуру.