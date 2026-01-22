<?php

namespace App\Services\Pomodoro;

use App\Models\PomodoroSession;
use App\Models\User;
use App\Models\Task;
use Carbon\Carbon;

class PomodoroService
{
    public function start(User $user, array $data): PomodoroSession
    {
        $workSec = ($data['work_minutes'] ?? 25) * 60;
        $breakSec = ($data['break_minutes'] ?? 5) * 60;
        $pomodoros = $data['pomodoros'] ?? 4;

        $session = $user->pomodoroSessions()->create([
            'task_id' => $data['task_id'] ?? null,
            'work_sec' => $workSec,
            'break_sec' => $breakSec,
            'total_pomodoros' => $pomodoros,
            'completed_pomodoros' => 0,
            'status' => 'running',
            'phase' => 'work',
            'started_at' => Carbon::now(),
            'phase_started_at' => Carbon::now(),
            'synced_seconds' => 0,
        ]);

        return $session;
    }

    public function stop(PomodoroSession $session): PomodoroSession
    {
        $session->status = 'finished';
        $session->ended_at = Carbon::now();
        // compute elapsed and only add delta since last synced_seconds
        try {
            $phaseStart = $session->phase_started_at ? Carbon::parse($session->phase_started_at) : null;
            $ended = $session->ended_at ? Carbon::parse($session->ended_at) : Carbon::now();
            if ($phaseStart && $session->task_id && $session->phase === 'work') {
                $elapsedSeconds = max(0, $ended->diffInSeconds($phaseStart));
                $synced = (int) ($session->synced_seconds ?? 0);
                $delta = max(0, $elapsedSeconds - $synced);
                $minutesToAdd = (int) floor($delta / 60);
                if ($minutesToAdd > 0) {
                    $task = Task::find($session->task_id);
                    if ($task) {
                        $task->actual_minutes = ($task->actual_minutes ?? 0) + $minutesToAdd;
                        $task->save();
                    }
                }
                $session->synced_seconds = $elapsedSeconds;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $session->save();

        return $session;
    }

    public function completePomodoro(PomodoroSession $session): PomodoroSession
    {
        $session->increment('completed_pomodoros');

        if ($session->completed_pomodoros >= $session->total_pomodoros) {
            $session->status = 'finished';
            $session->ended_at = Carbon::now();
        } else {
            $session->status = 'running';
            // Auto-switch to break phase
            $session->phase = 'break';
            $session->phase_started_at = Carbon::now();
            $session->synced_seconds = 0;
        }

        // Compute elapsed and add delta since last synced_seconds (covers completed pomodoro)
        try {
            $phaseStart = $session->phase_started_at ? Carbon::parse($session->phase_started_at) : null;
            $now = Carbon::now();
            if ($phaseStart && $session->task_id && $session->phase === 'work') {
                $elapsedSeconds = max(0, $now->diffInSeconds($phaseStart));
                $synced = (int) ($session->synced_seconds ?? 0);
                $delta = max(0, $elapsedSeconds - $synced);
                $minutesToAdd = (int) floor($delta / 60);
                if ($minutesToAdd > 0) {
                    $task = Task::find($session->task_id);
                    if ($task) {
                        $task->actual_minutes = ($task->actual_minutes ?? 0) + $minutesToAdd;
                        $task->save();
                    }
                }
                $session->synced_seconds = $elapsedSeconds;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $session->save();

        return $session;
    }

    public function pauseSession(PomodoroSession $session): PomodoroSession
    {
        if ($session->paused_at === null) {
            $session->paused_at = Carbon::now();
        }
        $session->save();

        return $session;
    }

    public function resumeSession(PomodoroSession $session): PomodoroSession
    {
        if ($session->paused_at !== null) {
            $pausedDuration = Carbon::parse($session->paused_at)->diffInSeconds(Carbon::now());
            // shift phase_started_at forward by paused duration
            $session->phase_started_at = Carbon::parse($session->phase_started_at)->addSeconds($pausedDuration);
            $session->paused_at = null;
        }
        $session->save();

        return $session;
    }

    public function completeBreak(PomodoroSession $session): PomodoroSession
    {
        // Switch back to work phase
        $session->phase = 'work';
        $session->phase_started_at = Carbon::now();
        $session->synced_seconds = 0;
        $session->save();

        return $session;
    }
}
