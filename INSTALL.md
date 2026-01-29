# Установка Tomodoro

## Быстрая установка (одна команда)

### Windows
```bash
install.bat
```

### Linux / macOS
```bash
chmod +x install.sh
./install.sh
```

## Что делают скрипты

### install.bat (Windows)
- ✅ Устанавливает Chocolatey (если требуется)
- ✅ Устанавливает PHP 8.2
- ✅ Устанавливает Node.js 20 LTS
- ✅ Устанавливает Composer
- ✅ Устанавливает PHP зависимости (`composer install`)
- ✅ Устанавливает Node.js зависимости (`npm install`)
- ✅ Создает `.env` файл конфигурации
- ✅ Генерирует ключ приложения
- ✅ Запускает миграции БД
- ✅ Собирает фронтенд (`npm run build`)
- ✅ Запускает сервер на http://localhost:8000

### install.sh (Linux/macOS)
- ✅ Определяет тип ОС (Ubuntu/Debian, CentOS/RHEL, macOS)
- ✅ Устанавливает PHP 8.2 с необходимыми расширениями
- ✅ Устанавливает Node.js 20 LTS
- ✅ Устанавливает NPM (отдельно для Debian/Ubuntu)
- ✅ Устанавливает Composer
- ✅ Устанавливает PHP зависимости
- ✅ Устанавливает Node.js зависимости
- ✅ Создает БД (SQLite)
- ✅ Запускает все миграции
- ✅ Собирает фронтенд
- ✅ Запускает сервер на http://localhost:8000

## Требования

### Минимум
- **Windows**: Windows 10+ или Windows Server 2016+
- **Linux**: Ubuntu 20.04+, Debian 11+, CentOS 7+
- **macOS**: macOS 10.15+
- **Интернет**: Требуется для загрузки пакетов

### Что будет установлено
- PHP 8.2+
- Node.js 20 LTS
- npm 10+
- Composer 2.6+
- SQLite3

## Обработка ошибок

Оба скрипта имеют встроенную обработку ошибок:

- **Прерывание при ошибке**: Если что-то не установилось, скрипт остановится и покажет ошибку
- **Логирование**: Ошибки сохраняются в логах:
  - Linux/macOS: `/tmp/composer.log`, `/tmp/npm.log`, `/tmp/npm-build.log`
  - Windows: Выводятся в консоль
- **Понятные сообщения**: Каждый этап показывает статус установки

## Поддерживаемые платформы

### Windows
- Windows 10
- Windows 11
- Windows Server 2016+

### Linux (Ubuntu/Debian)
- Ubuntu 20.04+
- Ubuntu 22.04+
- Ubuntu 24.04+
- Debian 11+
- Debian 12+

### Linux (CentOS/RHEL)
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

## Проблемы и решения

### Windows
**Проблема**: "Это не внутренняя или внешняя команда"
- **Решение**: Перезагрузите консоль после установки Chocolatey

**Проблема**: "Требуются права администратора"
- **Решение**: Скрипт автоматически запросит права админа

### Linux/macOS
**Проблема**: "Permission denied"
- **Решение**: Запустите `chmod +x install.sh`

**Проблема**: "Could not resolve host"
- **Решение**: Проверьте интернет-соединение

**Проблема**: "Your requirements could not be resolved"
- **Решение**: Обновите Composer: `composer self-update`

## После установки

1. Откройте браузер: http://localhost:8000
2. Приложение будет готово к использованию
3. Учетные данные по умолчанию см. в [docs/QUICK_START.md](docs/QUICK_START.md)

## Для разработчиков

Если нужна ручная установка, см. [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)

## Поддержка

Если у вас возникли проблемы:
1. Проверьте требования выше
2. Посмотрите логи ошибок
3. Откройте issue на GitHub

---

**Версия**: 1.0
**Последнее обновление**: 2024
