# Tomodoro · фокус-сервис

Tomodoro — однооконный продуктивный воркспейс: **задачи** с богатым описанием, чек-листами и файлами,
**помодоро-таймер** прямо во время работы и **календарь** событий — всё на одной странице, без
лишних кликов.

> Проект полностью переосмыслен: убраны Tailwind и API, дизайн собран на собственных CSS-токенах,
> добавлена SPA-оболочка на Livewire 3 + Alpine, редактор описаний на Tiptap.

## Стек

- **PHP** 8.2+, **Laravel** 11
- **Livewire** 3 + Alpine.js (идёт вместе с Livewire)
- **Tiptap** (StarterKit + TaskList + Link) — rich-text редактор
- **Vite** 7 для сборки фронтенда
- **SQLite** в комплекте — никакой возни с БД
- Без Tailwind, без сторонних UI-фреймворков — только собственные стили в `resources/css/`

## Запуск с нуля

```bash
git clone <repo> tomodoro
cd tomodoro

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate:fresh --seed   # создаст демо-пользователя и задачи
php artisan storage:link

# параллельно сервер + Vite
composer dev
```

Демо-аккаунт: `demo@tomodoro.local` / `password123`.

## Структура

```
app/
  Livewire/
    Auth/             — Login, Register
    Workspace/        — TaskBoard, TaskDetail, CalendarView, Settings
    Pomodoro/         — FloatingTimer (глобальный плавающий виджет)
  Services/
    Pomodoro/         — PomodoroPlanner (расчёт количества помодоро) + PomodoroService
  Models/             — User, Project, Tag, Task (+ Checklist/Attachment),
                        PomodoroSession, CalendarEvent
resources/
  css/
    app.css           — entry: импортирует токены, базу и компоненты
    tokens.css        — палитра, отступы, радиусы, темы
    base.css          — сброс и типографика
    components/       — layout, controls, tasks, pomodoro,
                        calendar, settings, command-palette
  js/
    app.js            — точка входа, регистрация Alpine-стора темы и хоткеев
    editor.js         — обёртка Tiptap для Livewire
    pomodoro.js       — drag и тикалка плавающего таймера
    command-palette.js — ⌘K, навигация «g + клавиша»
  views/
    components/
      layouts/        — base, guest, app
      ui/             — button, input, checkbox, dropdown, modal,
                        toast-region, command-palette, …
    livewire/         — render-шаблоны Livewire-компонентов
routes/
  web.php             — все маршруты (`/login`, `/register`, `/app/...`)
```

## Основные принципы UX

Дизайн ориентирован на 10 эвристик Якоба Нильсена.

| Эвристика                     | Что есть в Tomodoro                                                  |
| ----------------------------- | -------------------------------------------------------------------- |
| Видимость статуса             | Прогресс-бар Livewire, тосты на любое действие, радиальный прогресс таймера |
| Соответствие реальному миру   | «Сегодня», «Входящие», «Помодоро», «Перерыв» — без жаргона           |
| Контроль и свобода            | Toast «Удалено» с кнопкой «Восстановить» (soft-delete у задач)       |
| Единообразие                  | Одна цветовая система, одни и те же `<x-ui.*>`-кирпичики везде       |
| Предотвращение ошибок         | Подтверждения через `wire:confirm`, валидация на сервере            |
| Узнаваемость                  | ⌘K-палитра, плавающий таймер не нужно «искать»                       |
| Гибкость и эффективность      | Хоткеи `g t/i/u/a/c/s`, `n` для новой задачи, `t` — помодоро          |
| Эстетичный минимализм         | Один акцент `#E5533A`, плотные списки, почти ноль декора             |
| Помощь при ошибках            | Все формы выводят `field__error`, тосты с человекочитаемыми сообщениями |
| Помощь и документация         | Раздел «Хоткеи» в Настройках                                         |

## Хоткеи

| Клавиши            | Действие                            |
| ------------------ | ----------------------------------- |
| `Ctrl/⌘ + K`, `/`  | Командная палитра / поиск           |
| `n`                | Новая задача                         |
| `t`                | Запуск помодоро                      |
| `g t`              | Перейти на «Сегодня»                 |
| `g i / u / a`      | Входящие / Предстоящие / Все         |
| `g c`              | Календарь                            |
| `g s`              | Настройки                            |
