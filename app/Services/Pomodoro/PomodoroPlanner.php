<?php

namespace App\Services\Pomodoro;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

/**
 * Расчёт количества помодоро для конкретной задачи.
 *
 * Логика:
 *   1. Если у задачи есть estimated_minutes → используем его.
 *   2. Иначе если есть due_at → берём (due_at − created_at) и обрезаем сверху разумным
 *      потолком (16 рабочих часов), чтобы у пользователя со старой задачей
 *      не получилось 200 помодоро.
 *   3. Иначе возвращаем дефолт из настроек.
 */
class PomodoroPlanner
{
    public const PLAN_FALLBACK_POMODOROS = 4;
    public const PLAN_MAX_POMODOROS = 24;

    public function plan(?Task $task, User $user): PomodoroPlan
    {
        $prefs = $user->pomodoro_preferences;
        $work = max(5, (int) ($prefs['work_minutes'] ?? 25));
        $shortBreak = max(1, (int) ($prefs['short_break_minutes'] ?? 5));
        $longBreak = max($shortBreak, (int) ($prefs['long_break_minutes'] ?? 15));
        $longEvery = max(2, (int) ($prefs['long_break_every'] ?? 4));

        if (! $task) {
            return new PomodoroPlan(
                workMinutes: $work,
                shortBreakMinutes: $shortBreak,
                longBreakMinutes: $longBreak,
                longBreakEvery: $longEvery,
                totalPomodoros: self::PLAN_FALLBACK_POMODOROS,
                source: 'default',
            );
        }

        $minutes = $this->resolveTaskMinutes($task);

        if ($minutes === null) {
            return new PomodoroPlan(
                workMinutes: $work,
                shortBreakMinutes: $shortBreak,
                longBreakMinutes: $longBreak,
                longBreakEvery: $longEvery,
                totalPomodoros: self::PLAN_FALLBACK_POMODOROS,
                source: 'fallback',
            );
        }

        $remaining = max(0, $minutes - (int) floor(($task->spent_seconds ?? 0) / 60));
        $count = max(1, (int) ceil($remaining / $work));
        $count = min($count, self::PLAN_MAX_POMODOROS);

        return new PomodoroPlan(
            workMinutes: $work,
            shortBreakMinutes: $shortBreak,
            longBreakMinutes: $longBreak,
            longBreakEvery: $longEvery,
            totalPomodoros: $count,
            source: $task->estimated_minutes ? 'estimate' : 'deadline',
        );
    }

    /**
     * Сколько чистых рабочих минут нужно ещё на задачу.
     * Возвращает null, если оценить невозможно (нет ни оценки, ни дедлайна в будущем).
     */
    protected function resolveTaskMinutes(Task $task): ?int
    {
        if ($task->estimated_minutes) {
            return (int) $task->estimated_minutes;
        }

        if ($task->due_at && $task->due_at->isFuture()) {
            $created = $task->created_at ?? Carbon::now()->subDay();
            $totalMinutes = max(0, (int) $created->diffInMinutes($task->due_at, false));

            // Не более 16 «чистых» часов работы — иначе план становится бессмысленным.
            return min($totalMinutes, 16 * 60);
        }

        return null;
    }
}
