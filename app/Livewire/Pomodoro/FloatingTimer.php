<?php

namespace App\Livewire\Pomodoro;

use App\Models\PomodoroSession;
use App\Models\Task;
use App\Services\Pomodoro\PomodoroPlanner;
use App\Services\Pomodoro\PomodoroService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FloatingTimer extends Component
{
    /** Видимость виджета (после явного «закрыть»). */
    public bool $visible = true;
    public bool $expanded = false;

    /** Состояние диалога «выбор задачи». */
    public bool $showLauncher = false;
    public ?int $launcherTaskId = null;
    public int $launcherPomodoros = 4;
    public int $launcherWorkMinutes = 25;
    public int $launcherShortBreak = 5;
    public int $launcherLongBreak = 15;
    public string $launcherPlanSource = 'default';

    public function render()
    {
        return view('livewire.pomodoro.floating-timer', [
            'session' => $this->activeSession(),
            'tasks' => $this->tasks(),
        ]);
    }

    protected function activeSession(): ?PomodoroSession
    {
        if (! Auth::check()) return null;
        return $this->service()->activeSession(Auth::user());
    }

    protected function tasks()
    {
        return Task::forUser(Auth::id())
            ->open()
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit(50)
            ->get(['id', 'title', 'due_at', 'estimated_minutes', 'spent_seconds']);
    }

    public function toggleExpand(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function hide(): void
    {
        $this->visible = false;
    }

    public function show(): void
    {
        $this->visible = true;
    }

    /* ===== Launcher (диалог настройки) ===== */

    public function openLauncher(?int $taskId = null): void
    {
        $this->launcherTaskId = $taskId;
        $this->recalcLauncher();
        $this->showLauncher = true;
        $this->visible = true;
        $this->expanded = true;
    }

    public function closeLauncher(): void
    {
        $this->showLauncher = false;
    }

    public function updatedLauncherTaskId(): void
    {
        $this->recalcLauncher();
    }

    protected function recalcLauncher(): void
    {
        $task = $this->launcherTaskId
            ? Task::forUser(Auth::id())->find($this->launcherTaskId)
            : null;

        $plan = app(PomodoroPlanner::class)->plan($task, Auth::user());
        $this->launcherPomodoros = $plan->totalPomodoros;
        $this->launcherWorkMinutes = $plan->workMinutes;
        $this->launcherShortBreak = $plan->shortBreakMinutes;
        $this->launcherLongBreak = $plan->longBreakMinutes;
        $this->launcherPlanSource = $plan->source;
    }

    public function startSession(): void
    {
        $task = $this->launcherTaskId
            ? Task::forUser(Auth::id())->find($this->launcherTaskId)
            : null;

        $plan = new \App\Services\Pomodoro\PomodoroPlan(
            workMinutes: max(5, (int) $this->launcherWorkMinutes),
            shortBreakMinutes: max(1, (int) $this->launcherShortBreak),
            longBreakMinutes: max(1, (int) $this->launcherLongBreak),
            longBreakEvery: max(2, (int) (Auth::user()->pomodoro_preferences['long_break_every'] ?? 4)),
            totalPomodoros: max(1, min(24, (int) $this->launcherPomodoros)),
            source: $this->launcherPlanSource,
        );

        $this->service()->start(Auth::user(), $task, $plan);

        $this->showLauncher = false;
        $this->expanded = true;

        $this->dispatch('toast',
            type: 'success',
            title: 'Помодоро запущен',
            message: $task?->title ?? 'Свободный фокус',
        );
    }

    /* ===== Управление активной сессией ===== */

    public function pause(): void
    {
        if ($s = $this->activeSession()) $this->service()->pause($s);
    }

    public function resume(): void
    {
        if ($s = $this->activeSession()) $this->service()->resume($s);
    }

    public function skip(): void
    {
        if ($s = $this->activeSession()) {
            $this->service()->skip($s);
            $this->dispatch('toast', type: 'info', title: 'Фаза переключена');
        }
    }

    public function stop(): void
    {
        if ($s = $this->activeSession()) {
            $this->service()->stop($s);
            $this->dispatch('toast', type: 'info', title: 'Сессия остановлена');
        }
    }

    /* ===== Heartbeat от клиента ===== */

    public function tick(): void
    {
        if ($s = $this->activeSession()) {
            $this->service()->syncSpent($s);
        }
    }

    public function phaseFinished(): void
    {
        if ($s = $this->activeSession()) {
            $this->service()->completePhase($s);
            $this->dispatch('toast',
                type: 'success',
                title: $s->isWorking() ? 'Перерыв окончен' : 'Помодоро завершено',
            );
        }
    }

    /* ===== Внешние события ===== */

    #[On('pomodoro:start')]
    public function onPomodoroStart(?int $taskId = null): void
    {
        $this->openLauncher($taskId);
    }

    protected function service(): PomodoroService
    {
        return app(PomodoroService::class);
    }
}
