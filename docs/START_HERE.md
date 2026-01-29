# 🚀 С ЧЕГО НАЧАТЬ?

Вы только что скачали Tomodoro. Вот что делать дальше:

---

## 📖 Шаг 1: Прочитайте README (2 минуты)

Откройте [README.md](README.md) — это главная страница проекта.

Она расскажет вам:
- Что такое Tomodoro?
- Какие функции?
- Какие требования?

---

## 🗺️ Шаг 2: Выберите ваш путь

Откройте [docs/INDEX.md](docs/INDEX.md) и выберите нужный документ:

### 👤 Я хочу просто использовать приложение
→ Читайте: [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md) + [docs/USER_MANUAL.md](docs/USER_MANUAL.md)

### 🔧 Я администратор / системный администратор
→ Читайте: [docs/SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md) + [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md)

### 👨‍💻 Я разработчик / хочу расширять приложение
→ Читайте: [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md) + [docs/TECHNICAL_OVERVIEW.md](docs/TECHNICAL_OVERVIEW.md) + [docs/API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md)

---

## ⚡ Шаг 3: Установите (2 минуты)

### Windows
```bash
install.bat
```
Двойной клик и всё готово!

### Linux / macOS
```bash
chmod +x install.sh && ./install.sh
```

После установки откройте: **http://localhost:8000**

Подробнее: [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md)

---

## 💡 Быстрые ссылки

| Нужно | Документ |
|------|----------|
| Как начать? | [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md) |
| Как использовать? | [docs/USER_MANUAL.md](docs/USER_MANUAL.md) |
| FAQ? | [docs/USER_MANUAL.md#часто-задаваемые-вопросы](docs/USER_MANUAL.md#часто-задаваемые-вопросы-faq) |
| Требования? | [docs/SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md) |
| Что установить? | [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) |
| Что не так? | [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) (диагностика) |
| API? | [docs/API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md) |
| Архитектура? | [docs/TECHNICAL_OVERVIEW.md](docs/TECHNICAL_OVERVIEW.md) |

---

## 🎓 Основные файлы проекта

```
tomodoro/
├── 📄 README.md                 ← Начните отсюда!
├── 🚀 install.bat               ← Windows установка (двойной клик)
├── 🚀 install.sh                ← Linux/Mac установка
├── 📦 composer.json             ← PHP зависимости
├── 📦 package.json              ← JavaScript зависимости
│
├── docs/                        ← Документация
│   ├── 📚 INDEX.md              ← Справочник всей документации
│   ├── 🚀 QUICK_SETUP.md        ← Быстрая установка
│   ├── 📖 USER_MANUAL.md        ← Полное руководство
│   ├── 📋 INSTALLATION_GUIDE.md ← Детальная установка
│   ├── 📋 SYSTEM_REQUIREMENTS.md← Требования
│   ├── 💻 TECHNICAL_OVERVIEW.md ← Архитектура
│   ├── 🔌 API_DOCUMENTATION.md  ← API endpoints
│   └── ℹ️ LICENSE_AND_SUPPORT.md ← Лицензия
│
├── app/                        ← Исходный код
├── config/                     ← Конфигурация
├── database/                   ← Миграции БД
├── resources/                  ← Frontend
├── routes/                     ← Маршруты
└── tests/                      ← Тесты
```

---

## ⏱️ Время на освоение

| Уровень | Время | Что прочитать |
|---------|-------|---------------|
| ⚡ Экспресс (5 мин) | 5 мин | README + QUICK_SETUP |
| 🚀 Быстро (20 мин) | 20 мин | README + QUICK_SETUP + USER_MANUAL (основное) |
| 📚 Полностью (2 ч) | 2 ч | Все документы |
| 👨‍💻 Разработка (4 ч) | 4 ч | README + QUICK_SETUP + TECHNICAL_OVERVIEW + API |

---

## ✨ Ключевые функции Tomodoro

1. **Pomodoro Timer** ⏱️
   - Работайте 25 минут, отдыхайте 5
   - Отслеживайте прогресс

2. **Управление задачами** ✅
   - Создавайте задачи
   - Категоризируйте и приоритизируйте

3. **Календарь** 📅
   - Видьте вашу активность

4. **Статистика** 📊
   - Анализируйте производительность

---

## 🔧 Требования

- **Пользователи:** Браузер + Интернет
- **Разработчики:** PHP 8.2+, Node.js 20+, Composer

Подробнее: [docs/SYSTEM_REQUIREMENTS.md](docs/SYSTEM_REQUIREMENTS.md)

---

## 🆘 Что-то не работает?

1. Проверьте [docs/INSTALLATION_GUIDE.md](docs/INSTALLATION_GUIDE.md) (раздел диагностика)
2. Прочитайте [docs/USER_MANUAL.md#часто-задаваемые-вопросы](docs/USER_MANUAL.md#часто-задаваемые-вопросы-faq)
3. Откройте Issue на GitHub
4. Напишите на support email (см. [docs/LICENSE_AND_SUPPORT.md](docs/LICENSE_AND_SUPPORT.md))

---

## 🎯 Следующие шаги

```
1. Установите приложение
   ↓
2. Создайте первую задачу
   ↓
3. Запустите Pomodoro таймер
   ↓
4. Завершите задачу
   ↓
5. Посмотрите статистику
   ↓
6. 🎉 Повысьте производительность!
```

---

## 📞 Где взять помощь?

- 📖 **Документация**: [docs/INDEX.md](docs/INDEX.md)
- 💬 **FAQ**: [docs/USER_MANUAL.md](docs/USER_MANUAL.md)
- 🐛 **Проблемы**: GitHub Issues
- 📧 **Email**: support@example.com

---

**Готовы начать? Откройте [docs/QUICK_SETUP.md](docs/QUICK_SETUP.md) прямо сейчас!** 🚀

Добро пожаловать в Tomodoro! ✨
