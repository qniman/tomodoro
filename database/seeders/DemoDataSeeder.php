<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\PomodoroSession;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrFail();

        $tags = Tag::factory()->count(5)->for($user)->create();
        $tasks = Task::factory()->count(8)->for($user)->create();

        foreach ($tasks as $index => $task) {
            $task->tags()->sync($tags->random(2)->pluck('id'));

            if ($index % 2 === 0) {
                PomodoroSession::factory()
                    ->for($user)
                    ->for($task)
                    ->create();
            }
        }

        CalendarEvent::factory()
            ->count(5)
            ->for($user)
            ->create()
            ->each(function (CalendarEvent $event) use ($tasks) {
                $event->task()->associate($tasks->random())->save();
            });
    }
}
