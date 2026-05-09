<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'demo@tomodoro.local'],
            ['name' => 'Алексей Демо', 'password' => Hash::make('password123')]
        );

        $projects = [];
        foreach ([
            ['name' => 'Работа',   'color' => '#5b8def'],
            ['name' => 'Личное',   'color' => '#e5533a'],
            ['name' => 'Учёба',    'color' => '#2ea043'],
            ['name' => 'Дом',      'color' => '#f59e0b'],
            ['name' => 'Здоровье', 'color' => '#8b5cf6'],
            ['name' => 'Финансы',  'color' => '#10b981'],
        ] as $i => $p) {
            $projects[$p['name']] = Project::updateOrCreate(
                ['user_id' => $user->id, 'name' => $p['name']],
                ['color' => $p['color'], 'position' => ($i + 1) * 10]
            );
        }

        $tags = [];
        foreach ([
            ['name' => 'срочно',       'color' => '#e5533a'],
            ['name' => 'фокус',        'color' => '#5b8def'],
            ['name' => 'ожидание',     'color' => '#f59e0b'],
            ['name' => 'тексты',       'color' => '#8b5cf6'],
            ['name' => 'встреча',      'color' => '#10b981'],
            ['name' => 'исследование', 'color' => '#3b82f6'],
        ] as $t) {
            $tags[$t['name']] = Tag::updateOrCreate(
                ['user_id' => $user->id, 'name' => $t['name']],
                ['color' => $t['color']]
            );
        }

        $now = Carbon::now();

        Task::where('user_id', $user->id)->delete();
        CalendarEvent::where('user_id', $user->id)->delete();

        $taskDefs = $this->buildTasks($now);
        $position = 10;

        foreach ($taskDefs as $row) {
            $proj = $row['project'] ? ($projects[$row['project']] ?? null) : null;

            $task = Task::create([
                'user_id'            => $user->id,
                'project_id'         => $proj?->id,
                'title'              => $row['title'],
                'priority'           => $row['priority'] ?? 'normal',
                'due_at'             => $row['due_at'] ?? null,
                'all_day'            => $row['all_day'] ?? false,
                'estimated_minutes'  => $row['est'] ?? null,
                'is_pinned'          => $row['pinned'] ?? false,
                'completed_at'       => $row['completed'] ?? null,
                'spent_seconds'      => $row['spent_seconds'] ?? 0,
                'completed_pomodoros'=> $row['pomodoros'] ?? 0,
                'position'           => $position,
                'created_at'         => $row['created_at'] ?? $now,
                'updated_at'         => $row['completed'] ?? ($row['created_at'] ?? $now),
            ]);

            $position += 10;

            if (! empty($row['tags'])) {
                $task->tags()->sync(
                    collect($row['tags'])->map(fn ($name) => $tags[$name]->id)->all()
                );
            }

            foreach (($row['checklist'] ?? []) as $idx => [$label, $done]) {
                TaskChecklistItem::create([
                    'task_id'  => $task->id,
                    'label'    => $label,
                    'is_done'  => $done,
                    'position' => ($idx + 1) * 10,
                ]);
            }
        }

        foreach ($this->buildEvents($now, $user->id) as $ev) {
            CalendarEvent::create($ev);
        }

        $taskCount  = Task::where('user_id', $user->id)->count();
        $eventCount = CalendarEvent::where('user_id', $user->id)->count();

        $this->command->info('Demo seed completed:');
        $this->command->info('  User:   demo@tomodoro.local / password123');
        $this->command->info('  Tasks:  ' . $taskCount);
        $this->command->info('  Events: ' . $eventCount);
    }

    private function buildTasks(Carbon $now): array
    {
        $tasks = [];

        // ─── 2 ГОДА НАЗАД ────────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            $this->done('Настроить первый VPS сервер',          'Работа',   'high',   $now->copy()->subMonths(24)->subDays(5),  120, 4, 'фокус'),
            $this->done('Зарегистрировать домен tomodoro.ru',   'Работа',   'normal', $now->copy()->subMonths(24)->subDays(3),   15, 1),
            $this->done('Создать первый прототип приложения',   'Работа',   'urgent', $now->copy()->subMonths(23)->subDays(10), 480, 8, 'фокус'),
            $this->done('Настроить Laravel + Livewire',         'Учёба',    'high',   $now->copy()->subMonths(23)->subDays(5),  240, 4, 'исследование'),
            $this->done('Прочитать "Clean Code"',               'Учёба',    'normal', $now->copy()->subMonths(23),              180, 3, 'исследование'),
            $this->done('Купить рабочий стол и кресло',        'Дом',      'normal', $now->copy()->subMonths(22)->subDays(12),  60, 1),
            $this->done('Пройти курс по PostgreSQL',            'Учёба',    'high',   $now->copy()->subMonths(22)->subDays(3),  360, 6, 'исследование'),
            $this->done('Завести бюджет в таблице',             'Финансы',  'normal', $now->copy()->subMonths(21)->subDays(20),  45, 1),
            $this->done('Начать бегать по утрам (1 месяц)',     'Здоровье', 'normal', $now->copy()->subMonths(21)->subDays(5),   60, 2),
            $this->done('Выступить на локальном митапе',        'Работа',   'high',   $now->copy()->subMonths(20)->subDays(8),  120, 2, 'встреча'),
        ]);

        // ─── 18 МЕСЯЦЕВ НАЗАД ────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            $this->done('Запустить MVP продукта',               'Работа',   'urgent', $now->copy()->subMonths(18)->subDays(15), 600, 10, 'фокус'),
            $this->done('Написать документацию к API v1',       'Работа',   'normal', $now->copy()->subMonths(18)->subDays(10), 240, 4,  'тексты'),
            $this->done('Первый реальный клиент — онбординг',   'Работа',   'high',   $now->copy()->subMonths(17)->subDays(20),  90, 2,  'встреча'),
            $this->done('Пройти курс по Redis',                 'Учёба',    'normal', $now->copy()->subMonths(17)->subDays(5),  180, 3,  'исследование'),
            $this->done('Купить годовой абонемент в зал',       'Здоровье', 'normal', $now->copy()->subMonths(17),              15,  1),
            $this->done('Оптимизировать SQL-запросы главной',   'Работа',   'high',   $now->copy()->subMonths(16)->subDays(18), 120, 2,  'фокус'),
            $this->done('Подать декларацию за предыдущий год',  'Финансы',  'urgent', $now->copy()->subMonths(16)->subDays(3),   90, 2,  'срочно'),
            $this->done('Отремонтировать кухню',                'Дом',      'normal', $now->copy()->subMonths(15)->subDays(20), 480, 1),
            $this->done('Прочитать "DDIA" (Designing Data-Intensive Applications)', 'Учёба', 'high', $now->copy()->subMonths(15)->subDays(10), 600, 10, 'исследование'),
            $this->done('Написать статью на Хабр',              'Личное',   'normal', $now->copy()->subMonths(15)->subDays(2),  120, 2,  'тексты'),
        ]);

        // ─── 12 МЕСЯЦЕВ НАЗАД ────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            $this->done('Релиз v1.0 — публичный запуск',        'Работа',   'urgent', $now->copy()->subMonths(12)->subDays(20), 480, 8,  'фокус'),
            $this->done('Настроить мониторинг (Grafana+Prometheus)', 'Работа', 'high', $now->copy()->subMonths(12)->subDays(10), 240, 4, 'фокус'),
            $this->done('Купить MacBook Pro M2',                 'Личное',   'high',   $now->copy()->subMonths(11)->subDays(25),  30, 1),
            $this->done('Переехать на новую квартиру',           'Дом',      'urgent', $now->copy()->subMonths(11)->subDays(15), 480, 1),
            $this->done('Пройти курс по Docker + Kubernetes',    'Учёба',    'high',   $now->copy()->subMonths(11)->subDays(5),  360, 6,  'исследование'),
            $this->done('Нанять первого разработчика',           'Работа',   'high',   $now->copy()->subMonths(10)->subDays(20), 180, 3,  'встреча'),
            $this->done('Внедрить CI/CD',                        'Работа',   'normal', $now->copy()->subMonths(10)->subDays(10), 240, 4,  'фокус'),
            $this->done('Пройти диспансеризацию',               'Здоровье', 'normal', $now->copy()->subMonths(10)->subDays(3),   60, 1),
            $this->done('Начать вести финансовый журнал',        'Финансы',  'normal', $now->copy()->subMonths(9)->subDays(20),   30, 1),
            $this->done('Подготовить инвест-питч для раунда A',  'Работа',   'urgent', $now->copy()->subMonths(9)->subDays(5),   480, 8,  'фокус'),
        ]);

        // ─── 6 МЕСЯЦЕВ НАЗАД ─────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            $this->done('Рефакторинг монолита: разбить на сервисы', 'Работа', 'high', $now->copy()->subMonths(6)->subDays(25), 600, 10, 'фокус'),
            $this->done('Прочитать "Pragmatic Programmer"',      'Учёба',    'normal', $now->copy()->subMonths(6)->subDays(15), 240, 4,  'исследование'),
            $this->done('Купить велосипед',                      'Здоровье', 'normal', $now->copy()->subMonths(6)->subDays(8),   30, 1),
            $this->done('Подать заявку на конференцию DevConf',  'Работа',   'high',   $now->copy()->subMonths(5)->subDays(20),  60, 1,  'тексты'),
            $this->done('Релиз v1.5 — мобильная адаптация',     'Работа',   'urgent', $now->copy()->subMonths(5)->subDays(10), 480, 8,  'фокус'),
            $this->done('Настроить E2E-тесты (Playwright)',      'Работа',   'high',   $now->copy()->subMonths(5)->subDays(3),  240, 4,  'фокус'),
            $this->done('Пройти онлайн-курс по TypeScript',      'Учёба',    'normal', $now->copy()->subMonths(4)->subDays(20), 180, 3,  'исследование'),
            $this->done('Выступить на DevConf 2025',             'Работа',   'high',   $now->copy()->subMonths(4)->subDays(10), 240, 4,  'встреча'),
            $this->done('Сделать кухонный ремонт в квартире',    'Дом',      'normal', $now->copy()->subMonths(4)->subDays(3),  360, 1),
            $this->done('Оформить ИП',                           'Финансы',  'urgent', $now->copy()->subMonths(3)->subDays(25),  90, 2,  'срочно'),
        ]);

        // ─── ПОСЛЕДНИЕ 3 МЕСЯЦА ───────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            $this->done('Написать unit-тесты для модуля Auth',   'Работа',   'high',   $now->copy()->subMonths(3)->subDays(15), 180, 3,  'фокус'),
            $this->done('Настроить Redis для кэширования',       'Работа',   'high',   $now->copy()->subMonths(3)->subDays(8),  120, 2,  'фокус'),
            $this->done('Прочитать "Staff Engineer"',            'Учёба',    'normal', $now->copy()->subMonths(2)->subDays(25), 180, 3,  'исследование'),
            $this->done('Записаться на курс английского',        'Учёба',    'normal', $now->copy()->subMonths(2)->subDays(20),  15, 1),
            $this->done('Релиз v2.0',                            'Работа',   'urgent', $now->copy()->subMonths(2)->subDays(10), 480, 8,  'фокус'),
            $this->done('Настроить Horizon для очередей',        'Работа',   'normal', $now->copy()->subMonths(2)->subDays(3),   90, 2,  'фокус'),
            $this->done('Провести ретроспективу команды',        'Работа',   'normal', $now->copy()->subMonths(1)->subDays(20), 120, 2,  'встреча'),
            $this->done('Пройти медосмотр',                     'Здоровье', 'normal', $now->copy()->subMonths(1)->subDays(15),  60, 1),
            $this->done('Подать налоговый отчёт',               'Финансы',  'urgent', $now->copy()->subMonths(1)->subDays(5),   90, 2,  'срочно'),
            $this->done('Настроить Telescope для дебага',        'Работа',   'normal', $now->copy()->subDays(25),               60, 1,  'фокус'),
            $this->done('Провести код-ревью PR #38–41',          'Работа',   'high',   $now->copy()->subDays(20),               90, 2),
            $this->done('Обновить зависимости проекта',          'Работа',   'normal', $now->copy()->subDays(18),               45, 1),
            $this->done('Написать статью «Livewire 3 Tips»',    'Личное',   'normal', $now->copy()->subDays(15),               120, 2,  'тексты'),
            $this->done('Пробежка 5 км',                         'Здоровье', 'low',    $now->copy()->subDays(8),                35, 1),
            $this->done('Оплатить хостинг',                      'Финансы',  'normal', $now->copy()->subDays(5),                 5, 1),
            $this->done('Настроить VPN на офисном сервере',      'Работа',   'high',   $now->copy()->subDays(3),                60, 1),
            $this->done('Прочитать документацию по Alpine.js v3','Учёба',    'normal', $now->copy()->subDays(2),                90, 2,  'исследование'),
        ]);

        // ─── ПРОСРОЧЕННЫЕ ────────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            [
                'title'    => 'Отправить отчёт за прошлый квартал',
                'project'  => 'Работа',  'priority' => 'urgent',
                'due_at'   => $now->copy()->subDays(3)->setTime(17, 0),
                'est'      => 60,  'tags' => ['срочно'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(10),
            ],
            [
                'title'    => 'Переоформить страховку на машину',
                'project'  => 'Финансы', 'priority' => 'high',
                'due_at'   => $now->copy()->subDays(5)->setTime(12, 0),
                'est'      => 30,  'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subDays(12),
            ],
            [
                'title'    => 'Ответить на ревью книги для издательства',
                'project'  => 'Личное',  'priority' => 'normal',
                'due_at'   => $now->copy()->subDays(1)->setTime(23, 59),
                'est'      => 45,  'tags' => ['тексты'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(7),
            ],
        ]);

        // ─── СЕГОДНЯ ─────────────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            [
                'title'    => 'Провести код-ревью PR #42',
                'project'  => 'Работа',  'priority' => 'urgent',
                'due_at'   => $now->copy()->setTime(12, 0), 'pinned' => true,
                'est'      => 30, 'tags' => ['срочно', 'фокус'],
                'checklist' => [
                    ['Прочитать описание PR', true],
                    ['Проверить тесты', false],
                    ['Оставить комментарии', false],
                ],
                'created_at' => $now->copy()->subDays(1),
            ],
            [
                'title'    => 'Ответить на письма клиентам',
                'project'  => 'Работа',  'priority' => 'high',
                'due_at'   => $now->copy()->setTime(10, 0),
                'est'      => 45, 'tags' => ['встреча'],
                'checklist' => [
                    ['Клиент А — вопрос по оплате', false],
                    ['Клиент Б — техзадание',       false],
                    ['Клиент В — продление',        false],
                ],
                'created_at' => $now->copy()->subDays(1),
            ],
            [
                'title'    => 'Зафиксировать расходы за неделю',
                'project'  => 'Финансы', 'priority' => 'high',
                'due_at'   => $now->copy()->setTime(18, 0),
                'est'      => 20, 'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subDays(2),
            ],
            [
                'title'    => 'Сделать 10 минут растяжки',
                'project'  => 'Здоровье', 'priority' => 'low',
                'due_at'   => $now->copy()->setTime(8, 30),
                'est'      => 10, 'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subDays(1),
            ],
        ]);

        // ─── БЛИЖАЙШИЕ ДНИ / НЕДЕЛИ ──────────────────────────────────────────
        $tasks = array_merge($tasks, [
            [
                'title'    => 'Синхронизация с командой',
                'project'  => 'Работа',  'priority' => 'high',
                'due_at'   => $now->copy()->addDay()->setTime(10, 0),
                'est'      => 60, 'tags' => ['встреча'],
                'checklist' => [
                    ['Подготовить слайды',   false],
                    ['Записать вопросы',     false],
                    ['Обновить roadmap',     false],
                ],
                'created_at' => $now->copy()->subDays(3),
            ],
            [
                'title'    => 'Настроить CI/CD для нового сервиса',
                'project'  => 'Работа',  'priority' => 'urgent',
                'due_at'   => $now->copy()->addDays(3)->setTime(14, 0), 'pinned' => true,
                'est'      => 180, 'tags' => ['фокус'],
                'checklist' => [
                    ['Dockerfile',         false],
                    ['GitHub Actions',     false],
                    ['Тестовый прогон',    false],
                    ['Staging деплой',     false],
                ],
                'created_at' => $now->copy()->subDays(5),
            ],
            [
                'title'    => 'Написать технический пост для блога',
                'project'  => 'Личное',  'priority' => 'normal',
                'due_at'   => $now->copy()->addDays(2)->setTime(12, 0),
                'est'      => 120, 'tags' => ['тексты', 'фокус'],
                'checklist' => [
                    ['Набросать структуру',    false],
                    ['Написать черновик',      false],
                    ['Добавить примеры кода',  false],
                    ['Вычитать',               false],
                ],
                'created_at' => $now->copy()->subDays(4),
            ],
            [
                'title'    => 'Оплатить коммунальные услуги',
                'project'  => 'Финансы', 'priority' => 'high',
                'due_at'   => $now->copy()->addDays(3)->setTime(23, 59),
                'est'      => 15, 'tags' => ['срочно'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(2),
            ],
            [
                'title'    => 'Подготовить презентацию для инвестора',
                'project'  => 'Работа',  'priority' => 'urgent',
                'due_at'   => $now->copy()->addDays(6)->setTime(9, 0), 'pinned' => true,
                'est'      => 240, 'tags' => ['фокус', 'срочно'],
                'checklist' => [
                    ['Структура (проблема, решение, рынок)', false],
                    ['Финансовая модель',                    false],
                    ['Слайд с командой',                     false],
                    ['Roadmap на год',                       false],
                    ['Репетиция',                            false],
                ],
                'created_at' => $now->copy()->subDays(7),
            ],
            [
                'title'    => 'Написать unit-тесты для нового API',
                'project'  => 'Работа',  'priority' => 'high',
                'due_at'   => $now->copy()->addDays(11)->setTime(16, 0),
                'est'      => 90, 'tags' => ['фокус'],
                'checklist' => [
                    ['Тесты для /auth',     false],
                    ['Тесты для /tasks',    false],
                    ['Тесты для /projects', false],
                ],
                'created_at' => $now->copy()->subDays(3),
            ],
            [
                'title'    => 'Рефакторинг модуля авторизации',
                'project'  => 'Работа',  'priority' => 'normal',
                'due_at'   => $now->copy()->addDays(8)->setTime(14, 0),
                'est'      => 120, 'tags' => ['фокус'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(5),
            ],
            [
                'title'    => 'Пройти чек-ап у стоматолога',
                'project'  => 'Здоровье', 'priority' => 'normal',
                'due_at'   => $now->copy()->addDays(10)->setTime(12, 0),
                'est'      => 60, 'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subDays(2),
            ],
            [
                'title'    => 'Пройти курс по Kubernetes (модуль 3)',
                'project'  => 'Учёба',   'priority' => 'normal',
                'due_at'   => $now->copy()->addDays(4)->setTime(20, 0),
                'est'      => 90, 'tags' => ['исследование'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(6),
            ],
        ]);

        // ─── БЕЗ ДАТЫ (Inbox) ─────────────────────────────────────────────────
        $tasks = array_merge($tasks, [
            [
                'title'    => 'Изучить Rust (начало)',
                'project'  => 'Учёба',   'priority' => 'low',
                'due_at'   => null, 'est' => 60, 'tags' => ['исследование'], 'checklist' => [],
                'created_at' => $now->copy()->subMonths(3),
            ],
            [
                'title'    => 'Написать README для open-source проекта',
                'project'  => 'Работа',  'priority' => 'normal',
                'due_at'   => null, 'est' => 45, 'tags' => ['тексты'], 'checklist' => [],
                'created_at' => $now->copy()->subMonths(1),
            ],
            [
                'title'    => 'Найти нового дизайнера',
                'project'  => 'Работа',  'priority' => 'high',
                'due_at'   => null, 'est' => 60, 'tags' => ['ожидание'],
                'checklist' => [
                    ['Разместить вакансию',        false],
                    ['Провести собеседования',     false],
                    ['Проверить портфолио',        false],
                ],
                'created_at' => $now->copy()->subDays(14),
            ],
            [
                'title'    => 'Настроить умный дом',
                'project'  => 'Дом',     'priority' => 'low',
                'due_at'   => null, 'est' => 120, 'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subMonths(5),
            ],
            [
                'title'    => 'Изучить GraphQL',
                'project'  => 'Учёба',   'priority' => 'normal',
                'due_at'   => null, 'est' => 180, 'tags' => ['исследование'], 'checklist' => [],
                'created_at' => $now->copy()->subMonths(2),
            ],
            [
                'title'    => 'Подписать договор с новым подрядчиком',
                'project'  => 'Работа',  'priority' => 'high',
                'due_at'   => null, 'est' => 30, 'tags' => ['ожидание'], 'checklist' => [],
                'created_at' => $now->copy()->subDays(10),
            ],
            [
                'title'    => 'Сделать резервную копию ноутбука',
                'project'  => 'Личное',  'priority' => 'normal',
                'due_at'   => null, 'est' => 20, 'tags' => [], 'checklist' => [],
                'created_at' => $now->copy()->subDays(20),
            ],
        ]);

        return $tasks;
    }

    private function done(
        string $title,
        string $project,
        string $priority,
        Carbon $completedAt,
        int $estMinutes = 60,
        int $pomodoros = 2,
        ?string $tag = null,
    ): array {
        $created = $completedAt->copy()->subDays(rand(1, 5));

        return [
            'title'         => $title,
            'project'       => $project,
            'priority'      => $priority,
            'due_at'        => $completedAt->copy()->setTime(rand(9, 18), 0),
            'est'           => $estMinutes,
            'tags'          => $tag ? [$tag] : [],
            'checklist'     => [],
            'completed'     => $completedAt,
            'spent_seconds' => $estMinutes * 55,
            'pomodoros'     => $pomodoros,
            'created_at'    => $created,
        ];
    }

    private function buildEvents(Carbon $now, int $userId): array
    {
        $events = [];

        $add = function (
            string $title,
            Carbon $start,
            int $durationMin,
            string $color,
            bool $allDay = false,
        ) use ($userId, &$events) {
            $events[] = [
                'user_id'     => $userId,
                'title'       => $title,
                'description' => null,
                'starts_at'   => $start,
                'ends_at'     => $start->copy()->addMinutes($durationMin),
                'all_day'     => $allDay,
                'color'       => $color,
            ];
        };

        // Прошлые события (история за 2 года)
        foreach (range(1, 24) as $m) {
            $base = $now->copy()->subMonths($m);
            $add('Стендап команды',       $base->copy()->setTime(9, 30), 30,  '#5b8def');
            $add('Ретроспектива спринта', $base->copy()->setTime(15, 0), 90,  '#f59e0b');
            if ($m % 3 === 0) {
                $add('Квартальное ревью',  $base->copy()->setTime(11, 0), 120, '#e5533a');
            }
        }

        // Прошлые разовые события
        $add('Конференция DevConf 2024',         $now->copy()->subMonths(16)->setTime(10, 0), 480,  '#3b82f6', true);
        $add('Первый клиент — демо',             $now->copy()->subMonths(18)->setTime(14, 0), 60,   '#e5533a');
        $add('Публичный запуск v1.0',            $now->copy()->subMonths(12)->setTime(12, 0), 120,  '#10b981');
        $add('Отпуск — Черногория (день 1)',     $now->copy()->subMonths(10)->setTime(0, 0),  1440, '#10b981', true);
        $add('Отпуск — Черногория (день 2)',     $now->copy()->subMonths(10)->addDays(1)->setTime(0, 0), 1440, '#10b981', true);
        $add('Отпуск — Черногория (день 3)',     $now->copy()->subMonths(10)->addDays(2)->setTime(0, 0), 1440, '#10b981', true);
        $add('Выступление на митапе',            $now->copy()->subMonths(8)->setTime(19, 0),  90,   '#8b5cf6');
        $add('Подписание договора с инвестором', $now->copy()->subMonths(6)->setTime(11, 0),  60,   '#e5533a');
        $add('DevConf 2025',                     $now->copy()->subMonths(4)->setTime(10, 0),  480,  '#3b82f6', true);
        $add('Релиз v2.0 — деплой',              $now->copy()->subMonths(2)->setTime(22, 0),  60,   '#e5533a');

        // Прошлые регулярные тренировки (последние 3 месяца, по ВТ/ЧТ/СБ)
        for ($w = 1; $w <= 12; $w++) {
            $monday = $now->copy()->subWeeks($w)->startOfWeek();
            $add('Тренировка', $monday->copy()->addDays(1)->setTime(7, 0), 60, '#8b5cf6');
            $add('Тренировка', $monday->copy()->addDays(3)->setTime(7, 0), 60, '#8b5cf6');
            $add('Тренировка', $monday->copy()->addDays(5)->setTime(9, 0), 60, '#8b5cf6');
        }

        // Предстоящие события
        $add('Стендап с командой',         $now->copy()->setTime(9, 30),            30,  '#5b8def');
        $add('Код-ревью с Петром',         $now->copy()->addDays(1)->setTime(11, 0), 60,  '#5b8def');
        $add('Тренировка',                 $now->copy()->addDays(1)->setTime(7, 0),  60,  '#8b5cf6');
        $add('Встреча с инвестором',       $now->copy()->addDays(2)->setTime(14, 0), 90,  '#e5533a');
        $add('Онлайн-курс: Kubernetes',    $now->copy()->addDays(3)->setTime(19, 0), 90,  '#2ea043');
        $add('Тренировка',                 $now->copy()->addDays(3)->setTime(7, 0),  60,  '#8b5cf6');
        $add('Ретро за квартал',           $now->copy()->addDays(4)->setTime(15, 0), 120, '#f59e0b');
        $add('Врач: терапевт',             $now->copy()->addDays(5)->setTime(10, 0), 60,  '#8b5cf6');
        $add('Тренировка',                 $now->copy()->addDays(5)->setTime(9, 0),  60,  '#8b5cf6');
        $add('День рождения Лены',         $now->copy()->addDays(6)->setTime(0, 0),  1440,'#ec4899', true);
        $add('Планёрка отдела',            $now->copy()->addDays(7)->setTime(10, 0), 60,  '#5b8def');
        $add('Звонок с клиентом (MVP)',    $now->copy()->addDays(8)->setTime(16, 0), 45,  '#e5533a');
        $add('Воркшоп по AI-инструментам', $now->copy()->addDays(9)->setTime(18, 0), 120, '#3b82f6');
        $add('Защита проекта',             $now->copy()->addDays(11)->setTime(13, 0), 90, '#2ea043');
        $add('Онлайн-митап Laravel',       $now->copy()->addDays(12)->setTime(20, 0), 60, '#f97316');
        $add('Семейный ужин',              $now->copy()->addDays(13)->setTime(19, 0), 120,'#ec4899');
        $add('Quarterly review',           $now->copy()->addDays(14)->setTime(11, 0), 90, '#5b8def');
        $add('Стоматолог',                 $now->copy()->addDays(15)->setTime(12, 0), 60, '#8b5cf6');
        $add('Деплой v2.3 на прод',        $now->copy()->addDays(16)->setTime(22, 0), 60, '#e5533a');
        $add('Отпуск',                     $now->copy()->addDays(20)->setTime(0, 0), 1440,'#10b981', true);
        $add('Отпуск',                     $now->copy()->addDays(21)->setTime(0, 0), 1440,'#10b981', true);
        $add('Отпуск',                     $now->copy()->addDays(22)->setTime(0, 0), 1440,'#10b981', true);

        return $events;
    }
}
