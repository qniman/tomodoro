# Развёртка на Windows

Этот гайд покрывает развёртку Tomodoro в среде Windows (локальная разработка и продакшн варианты).

## Вариант 1 — Рекомендуемый для продакшна: WSL2 + Docker

1. Установите WSL2 и дистрибутив Ubuntu из Microsoft Store.
2. Установите Docker Desktop (включите WSL2 интеграцию).
3. Запустите контейнеры с PHP, DB, Redis и Nginx по Docker Compose (предоставить `docker-compose.yml`).

## Вариант 2 — Локальная разработка (XAMPP / Laragon)

1. Установите PHP, MySQL и Composer (через Laragon или XAMPP для Windows).
2. Установите Node.js.
3. Склонируйте репозиторий, установите зависимости, настройте `.env` и выполните миграции как в разделе Quickstart.

## Планировщик (Scheduler)

- Используйте Windows Task Scheduler для запуска `php artisan schedule:run` каждую минуту.
- Для очередей используйте NSSM или создайте Windows Service, который запускает `php artisan queue:work`.

## Разрешения

- Обратите внимание на права доступа к `storage` и `bootstrap/cache`. При использовании WSL/Unix-подсистемы применяйте Unix-права внутри WSL.

---

Если нужен пример `docker-compose.yml` или конфигурация для Windows Service / NSSM — добавлю.