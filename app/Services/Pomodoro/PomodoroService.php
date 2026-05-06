<?php

namespace App\Services\Pomodoro;

use App\Models\PomodoroSession;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PomodoroService
{
    /**
     * Создаёт активную сессию. Если у пользователя уже есть незавершённая —
     * корректно её завершает (статус aborted) и стартует новую.
     */
    public function start(User $user, ?Task $task, PomodoroPlan $plan): PomodoroSession
    {
        return DB::transaction(function () use ($user, $task, $plan) {
            $this->abortRunning($user);

            $now = Carbon::now();

            return PomodoroSession::create([
                'user_id' => $user->id,
                'task_id' => $task?->id,
                'status' => PomodoroSession::STATUS_RUNNING,
                'phase' => PomodoroSession::PHASE_WORK,
                'work_seconds' => $plan->workMinutes * 60,
                'short_break_seconds' => $plan->shortBreakMinutes * 60,
                'long_break_seconds' => $plan->longBreakMinutes * 60,
                'long_break_every' => $plan->longBreakEvery,
                'total_pomodoros' => $plan->totalPomodoros,
                'completed_pomodoros' => 0,
                'phase_started_at' => $now,
                'started_at' => $now,
                'paused_at' => null,
                'synced_seconds' => 0,
            ]);
        });
    }

    public function pause(PomodoroSession $session): PomodoroSession
    {
        if ($session->paused_at !== null || $session->status !== PomodoroSession::STATUS_RUNNING) {
            return $session;
        }

        $this->syncSpent($session);

        $session->paused_at = Carbon::now();
        $session->status = PomodoroSession::STATUS_PAUSED;
        $session->save();

        return $session;
    }

    public function resume(PomodoroSession $session): PomodoroSession
    {
        if ($session->paused_at === null) {
            return $session;
        }

        $now = Carbon::now();
        $pausedFor = max(0, (int) $now->diffInSeconds($session->paused_at, false));

        // Сдвигаем начало фазы вперёд на длительность паузы.
        if ($session->phase_started_at) {
            $session->phase_started_at = $session->phase_started_at->copy()->addSeconds($pausedFor);
        }

        $session->paused_at = null;
        $session->status = PomodoroSession::STATUS_RUNNING;
        $session->save();

        return $session;
    }

    /**
     * Перейти к следующей фазе вручную (skip).
     */
    public function skip(PomodoroSession $session): PomodoroSession
    {
        return $this->advancePhase($session, completed: $session->isWorking());
    }

    /**
     * Естественное завершение фазы (по таймеру с клиента).
     */
    public function completePhase(PomodoroSession $session): PomodoroSession
    {
        return $this->advancePhase($session, completed: $session->isWorking());
    }

    public function stop(PomodoroSession $session, string $reason = 'finished'): PomodoroSession
    {
        $this->syncSpent($session);

        $session->status = match ($reason) {
            'aborted' => PomodoroSession::STATUS_ABORTED,
            default => PomodoroSession::STATUS_FINISHED,
        };
        $session->ended_at = Carbon::now();
        $session->paused_at = null;
        $session->save();

        return $session;
    }

    /**
     * Heartbeat от клиента: говорит «прошло elapsed секунд активной работы».
     * Учитываем только work-фазу и только реально новые секунды.
     */
    public function syncSpent(PomodoroSession $session): PomodoroSession
    {
        if (! $session->phase_started_at || ! $session->isWorking() || $session->paused_at) {
            return $session;
        }

        $now = Carbon::now();
        $elapsed = max(0, (int) $now->diffInSeconds($session->phase_started_at, false));
        $elapsed = min($elapsed, (int) $session->work_seconds);

        $delta = $elapsed - (int) $session->synced_seconds;
        if ($delta <= 0) {
            return $session;
        }

        $session->synced_seconds = $elapsed;
        $session->save();

        if ($session->task_id) {
            Task::where('id', $session->task_id)
                ->update([
                    'spent_seconds' => DB::raw('spent_seconds + '.$delta),
                ]);
        }

        return $session;
    }

    public function abortRunning(User $user): void
    {
        $existing = PomodoroSession::where('user_id', $user->id)
            ->whereIn('status', [PomodoroSession::STATUS_RUNNING, PomodoroSession::STATUS_PAUSED])
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            $this->stop($existing, 'aborted');
        }
    }

    public function activeSession(User $user): ?PomodoroSession
    {
        return PomodoroSession::with('task')
            ->where('user_id', $user->id)
            ->whereIn('status', [PomodoroSession::STATUS_RUNNING, PomodoroSession::STATUS_PAUSED])
            ->latest('id')
            ->first();
    }

    protected function advancePhase(PomodoroSession $session, bool $completed): PomodoroSession
    {
        $this->syncSpent($session);

        $now = Carbon::now();

        if ($completed) {
            $session->completed_pomodoros = (int) $session->completed_pomodoros + 1;
            if ($session->task_id) {
                Task::where('id', $session->task_id)->increment('completed_pomodoros');
            }
        }

        // Все помодоро отработали → завершаем сессию.
        if ($completed && $session->completed_pomodoros >= $session->total_pomodoros) {
            $session->status = PomodoroSession::STATUS_FINISHED;
            $session->ended_at = $now;
            $session->phase = PomodoroSession::PHASE_WORK;
            $session->phase_started_at = null;
            $session->paused_at = null;
            $session->save();
            return $session;
        }

        // Чередуем work ↔ break.
        if ($session->isWorking()) {
            $useLong = $session->long_break_every > 0
                && $session->completed_pomodoros > 0
                && $session->completed_pomodoros % $session->long_break_every === 0;

            $session->phase = $useLong
                ? PomodoroSession::PHASE_LONG_BREAK
                : PomodoroSession::PHASE_SHORT_BREAK;
        } else {
            $session->phase = PomodoroSession::PHASE_WORK;
        }

        $session->phase_started_at = $now;
        $session->paused_at = null;
        $session->synced_seconds = 0;
        $session->status = PomodoroSession::STATUS_RUNNING;
        $session->save();

        return $session;
    }
}
