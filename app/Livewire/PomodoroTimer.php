<?php

namespace App\Livewire;

use App\Models\PomodoroSession;
use App\Models\Task;
use App\Services\Pomodoro\PomodoroService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Carbon\Carbon;

class PomodoroTimer extends Component
{
    protected $listeners = ['syncProgress'];
    public array $config = [
        'work_minutes' => 25,
        'break_minutes' => 5,
        'pomodoros' => 4,
    ];

    public ?int $selectedTaskId = null;
    public ?int $recommendedPomodoros = null;
    public ?int $estimatedMinutes = null;
    public bool $showEstimateModal = false;
    public ?int $estimateTaskId = null;
    public ?int $estimateMinutesInput = null;

    public function render()
    {
        return view('livewire.pomodoro-timer', [
            'sessions' => $this->sessions(),
            'active' => $this->activeSession(),
            'tasks' => $this->tasks(),
        ]);
    }

    public function updatedSelectedTaskId($taskId): void
    {
        if (! $taskId) {
            $this->recommendedPomodoros = null;
            $this->estimatedMinutes = null;
            return;
        }

        $task = Task::where('user_id', Auth::id())->find($taskId);
        if (! $task) {
            return;
        }

        if ($task->est_minutes) {
            $this->applyRecommendation((int) $task->est_minutes, $task->due_at);
        } else {
            $this->estimateTaskId = $task->id;
            $this->estimateMinutesInput = null;
            $this->showEstimateModal = true;
        }
    }

    public function updatedConfigWorkMinutes(): void
    {
        $this->recalculateForSelectedTask();
    }

    public function updatedConfigBreakMinutes(): void
    {
        $this->recalculateForSelectedTask();
    }

    public function startSession()
    {
        $payload = [
            'task_id' => $this->selectedTaskId,
            'work_minutes' => $this->config['work_minutes'],
            'break_minutes' => $this->config['break_minutes'],
            'pomodoros' => $this->config['pomodoros'],
        ];

        $this->service()->start(Auth::user(), $payload);
        $this->dispatch('$refresh');
    }

    public function stopSession($sessionId)
    {
        $session = $this->sessionForUser($sessionId);

        $this->service()->stop($session);
        $this->dispatch('$refresh');
    }

    public function completePomodoro($sessionId)
    {
        $session = $this->sessionForUser($sessionId);

        $this->service()->completePomodoro($session);
        $this->dispatch('$refresh');
    }

    public function pauseSession($sessionId)
    {
        $session = $this->sessionForUser($sessionId);

        $this->service()->pauseSession($session);
        $this->dispatch('$refresh');
    }

    public function resumeSession($sessionId)
    {
        $session = $this->sessionForUser($sessionId);

        $this->service()->resumeSession($session);
        $this->dispatch('$refresh');
    }

    public function completeBreak($sessionId)
    {
        $session = $this->sessionForUser($sessionId);

        $this->service()->completeBreak($session);
        $this->dispatch('$refresh');
    }

    protected function sessions()
    {
        return PomodoroSession::where('user_id', Auth::id())->latest()->limit(6)->get();
    }

    protected function activeSession()
    {
        return PomodoroSession::where('user_id', Auth::id())->where('status', 'running')->latest()->first();
    }

    protected function tasks()
    {
        return Task::where('user_id', Auth::id())->get();
    }

    protected function sessionForUser($id): PomodoroSession
    {
        return PomodoroSession::where('user_id', Auth::id())->findOrFail($id);
    }

    protected function service(): PomodoroService
    {
        return app(PomodoroService::class);
    }

    protected function applyRecommendation(int $estimatedWorkMinutes, ?Carbon $dueAt = null): void
    {
        $workLength = max(1, (int) $this->config['work_minutes']);
        if ($dueAt && $dueAt->isFuture()) {
            $available = max(1, $dueAt->diffInMinutes(now()));
            $estimatedWorkMinutes = min($estimatedWorkMinutes, $available);
        }
        $pomodoros = max(1, (int) ceil($estimatedWorkMinutes / $workLength));

        $this->config['pomodoros'] = $pomodoros;
        $this->recommendedPomodoros = $pomodoros;
        $this->estimatedMinutes = $pomodoros * ($this->config['work_minutes'] + $this->config['break_minutes']) - $this->config['break_minutes'];
    }

    protected function recalculateForSelectedTask(): void
    {
        if (! $this->selectedTaskId) {
            return;
        }

        $task = Task::where('user_id', Auth::id())->find($this->selectedTaskId);
        if ($task && $task->est_minutes) {
            $this->applyRecommendation((int) $task->est_minutes, $task->due_at);
        }
    }

    public function saveEstimate(): void
    {
        $this->validate([
            'estimateMinutesInput' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        if (! $this->estimateTaskId) {
            return;
        }

        $task = Task::where('user_id', Auth::id())->findOrFail($this->estimateTaskId);
        $task->update(['est_minutes' => $this->estimateMinutesInput]);

        $this->showEstimateModal = false;
        $this->estimateMinutesInput = null;
        $this->updatedSelectedTaskId($task->id);
    }

    public function syncProgress($sessionId, $elapsedSeconds): void
    {
        $session = PomodoroSession::where('user_id', Auth::id())->find($sessionId);
        if (! $session) {
            return;
        }

        $elapsed = (int) $elapsedSeconds;
        $synced = (int) ($session->synced_seconds ?? 0);
        $delta = max(0, $elapsed - $synced);

        if ($delta > 0 && $session->task_id) {
            $minutesToAdd = (int) floor($delta / 60);
            if ($minutesToAdd > 0) {
                try {
                    $task = Task::find($session->task_id);
                    if ($task) {
                        $task->actual_minutes = ($task->actual_minutes ?? 0) + $minutesToAdd;
                        $task->save();
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            $session->synced_seconds = $elapsed;
            $session->save();
        }
    }
}
