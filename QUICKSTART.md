# 🚀 Начните за 30 секунд

## Linux / macOS

```bash
git clone https://github.com/qniman/tomodoro && cd tomodoro && chmod +x install.sh && ./install.sh
```

## Windows

```bash
git clone https://github.com/qniman/tomodoro
cd tomodoro
install.bat
```

## Готово! 🎉

Откройте браузер: **http://localhost:8000**

---

### Что установилось?

- ✅ PHP 8.3+ с расширениями (xml, dom, curl, mbstring, zip, **sqlite3**)
- ✅ Node.js 20 LTS + npm 10+
- ✅ Composer 2.6+
- ✅ Все зависимости (composer + npm)
- ✅ База данных (SQLite)
- ✅ Приложение готово к использованию

### Если что-то не работает

1. Проверьте что установилось:
   ```bash
   php -v
   php -m | grep sqlite3
   node -v
   npm -v
   composer -v
   ```

2. Посмотрите логи:
   ```bash
   cat /tmp/composer.log
   cat /tmp/npm.log
   cat /tmp/artisan-key.log
   ```

3. Попробуйте вручную:
   ```bash
   composer install
   npm install
   php artisan migrate
   npm run build
   php artisan serve
   ```

---

**Подробно**: [INSTALL_GUIDE_V31.md](INSTALL_GUIDE_V31.md)  
**Версия скрипта**: v3.2

