<?php

namespace Database\Seeders;

use App\Models\PomodoroSession;
use App\Models\Task;
use App\Support\UiIconSet;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Массовое наполнение БД всеми сущностями приложения (нагрузочный / QA).
 *
 * Порядок величин по умолчанию: тысячи проектов/тегов, десятки тысяч задач, сотни тысяч чек-пунктов и связей task_tag.
 *
 * Запуск:
 *
 * php artisan migrate:fresh
 * php artisan db:seed --class=MassSeeder
 *
 * Либо в DatabaseSeeder::$call подставить MassSeeder::class вместо DemoSeeder.
 */
final class MassSeeder extends Seeder
{
    private const USER_COUNT = 30;

    private const PROJECTS_PER_USER_MIN = 18;

    private const PROJECTS_PER_USER_MAX = 55;

    private const TAGS_PER_USER_MIN = 40;

    private const TAGS_PER_USER_MAX = 140;

    private const TASKS_PER_USER = 650;

    private const CALENDAR_EVENTS_PER_USER = 220;

    private const POMODORO_SESSIONS_PER_USER = 280;

    /** Вероятность %: у задачи появится чеклист. */
    private const TASK_HAS_CHECKLIST_CHANCE = 38;

    private const CHECKLIST_ITEMS_MAX = 9;

    private const TASK_HAS_ATTACHMENT_PERCENT = 4;

    /** Сколько открытых задач пробуем сделать подзадачами (пересчёт ниже ограничит реальную глубину). */
    private const PARENT_TASK_ABS_MIN = 120;

    private const TASK_INSERT_CHUNK = 420;

    private const LARGE_INSERT_CHUNK = 3000;

    private const HEX_POOL = [
        '#E5533A', '#F59F00', '#2EA043', '#5b8def', '#7950F2',
        '#E64980', '#20C997', '#4C6FFF', '#F76707', '#846EF7',
        '#087F5B', '#C92A2A', '#1864AB', '#5F3DC4', '#862E9C',
    ];

    /** @var list<string> */
    private array $iconKeys = [];

    public function run(): void
    {
        $faker = fake('ru_RU');
        $this->iconKeys = array_keys(UiIconSet::choices());

        foreach (range(1, self::USER_COUNT) as $slot) {
            DB::transaction(function () use ($faker, $slot): void {
                $this->seedUser($faker, $slot);
            });
        }

        $this->command?->info(sprintf(
            'MassSeeder: %d пользователей, порядка %s задач.',
            self::USER_COUNT,
            number_format(self::USER_COUNT * self::TASKS_PER_USER, 0, ',', "'")
        ));
    }

    /**
     * @param  Generator  $faker
     */
    private function seedUser(object $faker, int $slot): void
    {
        $nowTs = Carbon::now()->format('Y-m-d H:i:s');

        $userId = (int) DB::table('users')->insertGetId([
            'name' => sprintf('Нагрузка %03d · %s', $slot, $faker->lastName()),
            'email' => sprintf('stress+%05d@tomodoro.local', $slot),
            'email_verified_at' => $nowTs,
            'password' => Hash::make(sprintf('stress-%05d', $slot)),
            'password_is_placeholder' => false,
            'avatar_path' => null,
            'theme' => $faker->randomElement(['auto', 'light', 'dark']),
            'pomodoro_settings' => json_encode([
                'work_minutes' => $faker->randomElement([20, 25, 30, 45]),
                'short_break_minutes' => $faker->randomElement([3, 5, 7]),
                'long_break_minutes' => $faker->randomElement([15, 20, 30]),
                'long_break_every' => $faker->randomElement([3, 4, 5]),
                'sound' => $faker->boolean(80),
            ], JSON_THROW_ON_ERROR),
            'created_at' => $nowTs,
            'updated_at' => $nowTs,
        ]);

        $projectIds = $this->insertProjects($faker, $userId);
        $tagIds = $this->insertTags($faker, $userId);

        $this->insertTasksCascade($faker, $userId, $projectIds, $tagIds);
        $this->insertCalendarEvents($faker, $userId);
        $this->insertPomodoroSessions($faker, $userId);
        $this->assignRandomParents($faker, $userId);
    }

    /**
     * @param  Generator  $faker
     * @return list<int>
     */
    private function insertProjects(object $faker, int $userId): array
    {
        $batch = [];

        foreach (range(1, random_int(self::PROJECTS_PER_USER_MIN, self::PROJECTS_PER_USER_MAX)) as $i) {
            $batch[] = [
                'user_id' => $userId,
                'name' => sprintf('[%05d-%04d] %s', $userId % 100000, $i, Str::limit($faker->words(7, true), 246, '')),
                'color' => $this->pickHex(),
                'icon' => $faker->boolean(62) ? $faker->randomElement($this->iconKeys) : null,
                'position' => $i * 10,
                'is_archived' => $faker->boolean(21),
                'created_at' => $now = Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => $now,
            ];
        }

        DB::table('projects')->insert($batch);

        return self::recentIdsMatchingUser('projects', $userId, count($batch));
    }

    /**
     * @param  Generator  $faker
     * @return list<int>
     */
    private function insertTags(object $faker, int $userId): array
    {
        $batch = [];

        foreach (range(1, random_int(self::TAGS_PER_USER_MIN, self::TAGS_PER_USER_MAX)) as $i) {
            $slug = preg_replace('/\s+/u', '-', trim(implode('-', preg_split('/\s+/u', $faker->words(14, true)))));
            $name = mb_strtolower((string) $slug).'-'.$i.'-'.$userId;

            $batch[] = [
                'user_id' => $userId,
                'name' => Str::limit($name, 20, ''),
                'color' => $this->pickHex(),
                'icon' => $faker->boolean(41) ? $faker->randomElement($this->iconKeys) : null,
                'created_at' => $now = Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => $now,
            ];
        }

        DB::table('tags')->insert($batch);

        return self::recentIdsMatchingUser('tags', $userId, count($batch));
    }

    /**
     * @param  Generator  $faker
     * @param  list<int>  $projectIds
     * @param  list<int>  $tagIds
     */
    private function insertTasksCascade(object $faker, int $userId, array $projectIds, array $tagIds): void
    {
        $priorities = Task::PRIORITIES;
        $taskBatch = [];

        foreach (range(1, self::TASKS_PER_USER) as $_) {
            $projectFk = ($projectIds !== [] && mt_rand(1, 100) <= 82)
                ? $projectIds[array_rand($projectIds)]
                : null;

            $completed = $faker->boolean(36);
            $due = mt_rand(1, 100) <= 71
                ? Carbon::parse($faker->dateTimeBetween('-56 days', '+90 days'))
                : null;

            $est = mt_rand(1, 100) <= 61
                ? $faker->numberBetween(5, 520)
                : null;

            $spent = mt_rand(1, 100) <= 71 && $est !== null
                ? $faker->numberBetween(0, max(300, (int) $est * 120))
                : $faker->numberBetween(0, 97000);

            $htmlParagraph = sprintf(
                '<p>%s</p><ul>%s</ul>',
                implode("\n</p>\n<p>", $faker->paragraphs(mt_rand(1, 3), false)),
                $faker->boolean(62)
                    ? '<li>'.implode('</li><li>', $faker->sentences(mt_rand(0, 4), false)).'</li>'
                    : ''
            );

            $hasDesc = $faker->boolean(71);
            $descriptionHtml = $hasDesc ? $htmlParagraph : null;
            $descriptionText = null;
            if ($hasDesc) {
                $descriptionText = trim(html_entity_decode(strip_tags($htmlParagraph))) ?: null;
            }

            $taskBatch[] = [
                'user_id' => $userId,
                'project_id' => $projectFk,
                'parent_id' => null,
                'title' => Str::limit($faker->realText(mt_rand(26, 120)), 254, ''),
                'description_html' => $descriptionHtml,
                'description_text' => $descriptionText !== '' ? $descriptionText : null,
                'priority' => $faker->randomElement($priorities),
                'due_at' => $due?->format('Y-m-d H:i:s'),
                'reminder_at' => mt_rand(1, 100) <= 16
                    ? Carbon::parse($faker->dateTimeBetween('-21 days', '+40 days'))->format('Y-m-d H:i:s')
                    : null,
                'all_day' => false,
                'completed_at' => $completed
                    ? Carbon::parse($faker->dateTimeBetween('-520 days', 'now'))->format('Y-m-d H:i:s')
                    : null,
                'is_pinned' => (! $completed) && $faker->boolean(6),
                'estimated_minutes' => $est,
                'spent_seconds' => max(0, min(86400 * 200, $spent)),
                'completed_pomodoros' => $completed ? mt_rand(0, 22) : mt_rand(0, 12),
                'position' => mt_rand(0, 985000),
                'created_at' => Carbon::parse($faker->dateTimeBetween('-600 days', 'now'))->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];

            if (count($taskBatch) >= self::TASK_INSERT_CHUNK) {
                $this->flushOneTaskWave($faker, $userId, $taskBatch, $tagIds);
                $taskBatch = [];
            }
        }

        if ($taskBatch !== []) {
            $this->flushOneTaskWave($faker, $userId, $taskBatch, $tagIds);
        }
    }

    /**
     * @param  Generator  $faker
     * @param  array<int,array<string,mixed>>  $taskBatch
     * @param  list<int>  $tagIds
     */
    private function flushOneTaskWave(object $faker, int $userId, array $taskBatch, array $tagIds): void
    {
        DB::table('tasks')->insert($taskBatch);
        /** @var list<int> $ids */
        $ids = self::recentIdsMatchingUser('tasks', $userId, count($taskBatch));

        $checklists = [];

        foreach ($ids as $taskId) {
            if ($faker->boolean(self::TASK_HAS_CHECKLIST_CHANCE)) {
                foreach (range(1, mt_rand(1, self::CHECKLIST_ITEMS_MAX)) as $step) {
                    $checklists[] = [
                        'task_id' => $taskId,
                        'label' => Str::limit(implode(', ', array_slice(explode(' ', $faker->sentence(26)), 0, mt_rand(2, 9))), 249, ''),
                        'is_done' => $faker->boolean(53),
                        'position' => $step * 10,
                        'created_at' => $now = Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($checklists, self::LARGE_INSERT_CHUNK) as $chk) {
            if ($chk !== []) {
                DB::table('task_checklist_items')->insert($chk);
            }
        }

        $tagsPivot = [];

        foreach ($ids as $taskId) {
            foreach ($this->pickRandomTagIds($tagIds) as $tid) {
                $tagsPivot[] = ['task_id' => $taskId, 'tag_id' => $tid];
                if (count($tagsPivot) >= self::LARGE_INSERT_CHUNK) {
                    DB::table('task_tag')->insertOrIgnore($tagsPivot);
                    $tagsPivot = [];
                }
            }
        }

        if ($tagsPivot !== []) {
            DB::table('task_tag')->insertOrIgnore($tagsPivot);
        }

        $attachments = [];

        foreach ($ids as $taskId) {
            if ($faker->boolean(self::TASK_HAS_ATTACHMENT_PERCENT)) {
                $isImg = $faker->boolean(35);
                $attachments[] = [
                    'task_id' => $taskId,
                    'user_id' => $userId,
                    'disk' => 'local',
                    'path' => 'mass-seeder/'.Str::slug($faker->sentence(12)).'-'.$faker->uuid().'.'.($isImg ? 'webp' : 'pdf'),
                    'original_name' => Str::limit($faker->sentence(14), 200, ''),
                    'mime' => $isImg ? 'image/webp' : 'application/pdf',
                    'size' => $faker->numberBetween(1500, 9_845_672),
                    'created_at' => $now = Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => $now,
                ];

                if (count($attachments) >= self::LARGE_INSERT_CHUNK) {
                    DB::table('task_attachments')->insert($attachments);
                    $attachments = [];
                }
            }
        }

        if ($attachments !== []) {
            DB::table('task_attachments')->insert($attachments);
        }
    }

    /**
     * @param  list<int>  $tagIds
     * @return list<int>
     */
    private function pickRandomTagIds(array $tagIds): array
    {
        if ($tagIds === [] || random_int(1, 100) <= 43) {
            return [];
        }

        $cnt = count($tagIds);
        $lowWant = mt_rand(1, 100) <= 71 ? 1 : 2;
        $take = mt_rand(min($lowWant, $cnt), min($cnt, 8));

        $copy = [...$tagIds];
        shuffle($copy);

        return array_values(array_unique(array_slice($copy, 0, $take)));
    }

    /**
     * @param  Generator  $faker
     */
    private function insertCalendarEvents(object $faker, int $userId): void
    {
        foreach (range(1, self::CALENDAR_EVENTS_PER_USER) as $i) {
            $start = Carbon::parse($faker->dateTimeBetween('-210 days', '+240 days'));

            DB::table('calendar_events')->insert([
                'user_id' => $userId,
                'task_id' => null,
                'title' => Str::limit($faker->sentence(8), 255, ''),
                'description' => $faker->boolean(71) ? $faker->paragraph(4) : null,
                'starts_at' => $start->copy()->format('Y-m-d H:i:s'),
                'ends_at' => $faker->boolean(18)
                    ? $start->copy()->addMinutes(mt_rand(25, 200))->format('Y-m-d H:i:s')
                    : $start->copy()->addDays(mt_rand(1, 4))->format('Y-m-d H:i:s'),
                'all_day' => $faker->boolean(11),
                'color' => $this->pickHex(),
                'created_at' => $now = Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => $now,
            ]);
        }

        $taskPkList = Task::query()->where('user_id', $userId)->limit(620)->pluck('id');

        if ($taskPkList->isEmpty()) {
            return;
        }

        $eventIds = DB::table('calendar_events')->where('user_id', $userId)->pluck('id');

        if ($eventIds->isEmpty()) {
            return;
        }

        $n = min(max(35, min(115, $taskPkList->count())), $eventIds->count());
        foreach ($eventIds->random($n) as $evtId) {
            DB::table('calendar_events')
                ->where('id', $evtId)
                ->update([
                    'task_id' => $taskPkList->random(),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
        }

        unset($i);
    }

    /**
     * Только финальные статусы, без running/paused — чтобы у пользователя после сидирования не оставалось «активной» pomodoro-сессии.
     *
     * @param  Generator  $faker
     */
    private function insertPomodoroSessions(object $faker, int $userId): void
    {
        $tasks = Task::query()->where('user_id', $userId)->pluck('id');
        $taskPool = $tasks->all();

        foreach (range(1, self::POMODORO_SESSIONS_PER_USER) as $_) {
            $statusRoll = mt_rand(1, 100);

            $status = match (true) {
                $statusRoll <= 70 => PomodoroSession::STATUS_FINISHED,
                $statusRoll <= 94 => PomodoroSession::STATUS_ABORTED,
                default => PomodoroSession::STATUS_QUEUED,
            };

            $phase = mt_rand(1, 100) <= 71 ? PomodoroSession::PHASE_WORK : PomodoroSession::PHASE_SHORT_BREAK;

            $workSec = mt_rand(7, 10) * 60;
            $shortSec = mt_rand(2, 6) * 60;
            $longSec = mt_rand(10, 26) * 60;
            $every = mt_rand(2, 5);
            $total = mt_rand(1, 14);
            $done = mt_rand(0, $total);

            $started = Carbon::parse($faker->dateTimeBetween('-760 days', '-3 days'));

            $ended = in_array($status, [PomodoroSession::STATUS_FINISHED, PomodoroSession::STATUS_ABORTED], true)
                ? $started->copy()->addHours(mt_rand(1, 20))->format('Y-m-d H:i:s')
                : null;

            DB::table('pomodoro_sessions')->insert([
                'user_id' => $userId,
                'task_id' => $taskPool === [] ? null : $faker->randomElement($taskPool),
                'status' => $status,
                'phase' => $phase,
                'work_seconds' => $workSec,
                'short_break_seconds' => $shortSec,
                'long_break_seconds' => $longSec,
                'long_break_every' => $every,
                'total_pomodoros' => $total,
                'completed_pomodoros' => $done,
                'phase_started_at' => $started->copy()->addMinutes(mt_rand(0, 60))->format('Y-m-d H:i:s'),
                'paused_at' => null,
                'synced_seconds' => mt_rand(0, 3600),
                'started_at' => $started->format('Y-m-d H:i:s'),
                'ended_at' => $ended,
                'created_at' => $started->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Связывает подмножество открытых задач случайными родительскими id того же пользователя.
     *
     * @param  Generator  $faker
     */
    private function assignRandomParents(object $faker, int $userId): void
    {
        $limitTarget = random_int(self::PARENT_TASK_ABS_MIN, (int) max(self::PARENT_TASK_ABS_MIN, ceil(self::TASKS_PER_USER * 0.12)));

        Task::withoutEvents(function () use ($faker, $userId, $limitTarget): void {
            Task::query()
                ->where('user_id', $userId)
                ->whereNull('completed_at')
                ->whereNull('deleted_at')
                ->whereNull('parent_id')
                ->inRandomOrder()
                ->limit($limitTarget)
                ->cursor()
                ->each(function (Task $child) use ($faker, $userId): void {
                    if (! $faker->boolean(72)) {
                        return;
                    }

                    $possible = Task::query()
                        ->where('user_id', $userId)
                        ->whereNull('completed_at')
                        ->whereNull('deleted_at')
                        ->whereNull('parent_id')
                        ->where('id', '!=', $child->id)
                        ->inRandomOrder()
                        ->value('id');

                    if ($possible) {
                        $child->update(['parent_id' => $possible]);
                    }
                });
        });
    }

    private function pickHex(): string
    {
        return self::HEX_POOL[array_rand(self::HEX_POOL)];
    }

    /**
     * Совместимо с SQLite / MySQL: для multi-insert нельзя полагаться на {@see PDO::lastInsertId()}.
     *
     * Допущение — в активной транзакции только что вставили ровно $count строк этого user и нет параллельных вставок.
     *
     * @return list<int>
     */
    private static function recentIdsMatchingUser(string $table, int $userId, int $count): array
    {
        /** @phpstan-ignore return.type */
        return DB::table($table)
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->limit($count)
            ->pluck('id')
            ->reverse()
            ->values()
            ->all();
    }
}
