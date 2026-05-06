<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\TaskChecklistItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@tomodoro.local'],
            ['name' => 'Demo', 'password' => 'password123']
        );

        $work = Project::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Работа'],
            ['color' => '#5b8def', 'position' => 10]
        );
        $personal = Project::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Личное'],
            ['color' => '#e5533a', 'position' => 20]
        );
        $study = Project::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Учёба'],
            ['color' => '#2ea043', 'position' => 30]
        );

        $focus = Tag::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'фокус'],
            ['color' => '#e5533a']
        );
        $writing = Tag::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'тексты'],
            ['color' => '#5b8def']
        );

        $tasks = [
            [
                'title' => 'Сверстать главную страницу проекта',
                'project_id' => $work->id,
                'priority' => 'high',
                'due_at' => now()->addHours(3),
                'estimated_minutes' => 90,
                'is_pinned' => true,
                'description_html' => '<p>Подготовить макет в Figma и собрать первый прототип на Livewire.</p><ul><li>Хедер</li><li>Секция фич</li><li>Футер</li></ul>',
                'tags' => [$focus->id],
                'checklist' => [
                    'Сделать каркас секций',
                    'Подобрать иллюстрации',
                    'Доделать адаптив',
                ],
            ],
            [
                'title' => 'Написать заметку про продуктивность',
                'project_id' => $personal->id,
                'priority' => 'normal',
                'due_at' => now()->addDay()->setTime(20, 0),
                'estimated_minutes' => 45,
                'description_html' => '<p>Тема — как помодоро помогает не выгорать на длинных задачах.</p>',
                'tags' => [$writing->id],
                'checklist' => [],
            ],
            [
                'title' => 'Подготовиться к экзамену по системному дизайну',
                'project_id' => $study->id,
                'priority' => 'urgent',
                'due_at' => now()->addDays(3)->setTime(10, 0),
                'estimated_minutes' => 240,
                'description_html' => '<p>Повторить темы из списка лекций. Сделать конспект.</p>',
                'tags' => [],
                'checklist' => [
                    'Лекция 1: масштабирование',
                    'Лекция 2: кэши',
                    'Лекция 3: очереди',
                ],
            ],
            [
                'title' => 'Купить продуктов на неделю',
                'project_id' => $personal->id,
                'priority' => 'low',
                'due_at' => now()->setTime(19, 0),
                'estimated_minutes' => 30,
                'tags' => [],
                'checklist' => [],
            ],
            [
                'title' => 'Прочитать главу книги',
                'project_id' => null,
                'priority' => 'normal',
                'due_at' => null,
                'estimated_minutes' => 25,
                'tags' => [],
                'checklist' => [],
            ],
            [
                'title' => 'Просроченное письмо клиенту',
                'project_id' => $work->id,
                'priority' => 'high',
                'due_at' => now()->subDay()->setTime(16, 0),
                'estimated_minutes' => 15,
                'tags' => [],
                'checklist' => [],
            ],
        ];

        foreach ($tasks as $i => $row) {
            $task = Task::firstOrCreate(
                ['user_id' => $user->id, 'title' => $row['title']],
                [
                    'project_id' => $row['project_id'],
                    'priority' => $row['priority'],
                    'due_at' => $row['due_at'],
                    'estimated_minutes' => $row['estimated_minutes'],
                    'description_html' => $row['description_html'] ?? null,
                    'is_pinned' => $row['is_pinned'] ?? false,
                    'position' => ($i + 1) * 10,
                ]
            );

            if (! empty($row['tags'])) {
                $task->tags()->sync($row['tags']);
            }

            foreach (($row['checklist'] ?? []) as $idx => $label) {
                TaskChecklistItem::firstOrCreate(
                    ['task_id' => $task->id, 'label' => $label],
                    ['position' => ($idx + 1) * 10, 'is_done' => $idx === 0]
                );
            }
        }
    }
}
