<?php

namespace App\Livewire\Workspace;

use App\Events\WorkspaceChatMessageSent;
use App\Events\WorkspaceMemberStatusUpdated;
use App\Events\WorkspaceReactionSent;
use App\Events\WorkspaceTimerUpdated;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceMessage;
use App\Models\WorkspaceReaction;
use App\Models\WorkspaceSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Комната')]
class Room extends Component
{
    public Workspace $workspace;

    public string $chatInput = '';

    // Форма создания/редактирования комнаты
    public bool $showCreateModal = false;
    public string $roomName = '';

    // Форма настройки таймера
    public bool $showTimerModal = false;
    public int $timerWorkMinutes = 25;
    public int $timerBreakMinutes = 5;

    public function mount(Workspace $workspace): void
    {
        abort_unless($workspace->isMember(Auth::id()), 403);
        $this->workspace = $workspace;
        $this->touchMember();
    }

    public function render()
    {
        $this->touchMember();

        $members = $this->workspace->members()
            ->with('user')
            ->orderByRaw("CASE WHEN status = 'focus' THEN 0 WHEN status = 'pause' THEN 1 ELSE 2 END")
            ->orderBy('pomodoros_today', 'desc')
            ->get();

        $session = $this->workspace->activeSession;
        if ($session) {
            $session->loadMissing('startedBy');
            if ($session->remainingSeconds() === 0 && ! $session->isPaused()) {
                $session = null;
            }
        }

        $messages = $this->workspace->messages()
            ->with('user')
            ->where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->take(60)
            ->get();

        $myMember = $members->firstWhere('user_id', Auth::id());

        return view('livewire.workspace.room', [
            'members'  => $members,
            'session'  => $session,
            'messages' => $messages,
            'myMember' => $myMember,
        ]);
    }

    // ===== Статус =====

    public function setStatus(string $status): void
    {
        abort_unless(in_array($status, ['focus', 'pause', 'away'], true), 422);

        $member = $this->myMember();
        $member->status = $status;
        $member->status_updated_at = now();
        $member->save();

        broadcast(new WorkspaceMemberStatusUpdated($member))->toOthers();

        // Обновляем Alpine-стейт на своём клиенте (toOthers исключает нас из Echo)
        $this->dispatch('local-member-status', userId: Auth::id(), status: $status);
    }

    // ===== Общий таймер =====

    public function openTimerModal(): void
    {
        $this->showTimerModal = true;
    }

    public function startTimer(): void
    {
        $this->validate([
            'timerWorkMinutes'  => 'required|integer|min:1|max:120',
            'timerBreakMinutes' => 'required|integer|min:1|max:60',
        ]);

        // Удаляем предыдущие сессии
        $this->workspace->activeSession?->delete();

        $session = WorkspaceSession::create([
            'workspace_id'     => $this->workspace->id,
            'started_by'       => Auth::id(),
            'phase'            => 'work',
            'duration_seconds' => $this->timerWorkMinutes * 60,
            'started_at'       => now(),
        ]);

        $session->load('startedBy');

        $this->setStatus('focus');
        $this->showTimerModal = false;

        broadcast(new WorkspaceTimerUpdated($this->workspace->id, $session, 'started'));

        $this->dispatch('toast', type: 'success', title: 'Таймер запущен!',
            message: "Работаем {$this->timerWorkMinutes} мин. Поехали!");
    }

    public function pauseTimer(): void
    {
        $session = $this->workspace->activeSession;
        if (! $session || $session->isPaused()) return;

        $session->paused_at = now();
        $session->save();

        $this->setStatus('pause');

        broadcast(new WorkspaceTimerUpdated($this->workspace->id, $session->fresh(), 'paused'));
    }

    public function resumeTimer(): void
    {
        $session = $this->workspace->activeSession;
        if (! $session || ! $session->isPaused()) return;

        // Сдвигаем started_at вперёд на время паузы, чтобы remaining рассчитывался верно
        $pausedDuration = $session->paused_at->diffInSeconds(now());
        $session->started_at = $session->started_at->addSeconds($pausedDuration);
        $session->paused_at = null;
        $session->save();

        $this->setStatus('focus');

        broadcast(new WorkspaceTimerUpdated($this->workspace->id, $session->fresh(), 'resumed'));
    }

    public function stopTimer(): void
    {
        $session = $this->workspace->activeSession;
        if (! $session) return;

        $session->delete();

        $this->setStatus('pause');

        broadcast(new WorkspaceTimerUpdated($this->workspace->id, null, 'stopped'));

        $this->dispatch('toast', type: 'info', title: 'Таймер остановлен');
    }

    // Клиент уведомляет сервер, что таймер истёк (только автор сессии)
    public function timerFinished(int $sessionId): void
    {
        $session = WorkspaceSession::find($sessionId);
        if (! $session || $session->workspace_id !== $this->workspace->id) return;
        if ($session->started_by !== Auth::id()) return;

        $nextPhase = $session->phase === 'work' ? 'break' : 'work';
        $nextDuration = $nextPhase === 'work'
            ? $this->timerWorkMinutes * 60
            : $this->timerBreakMinutes * 60;

        $session->phase            = $nextPhase;
        $session->duration_seconds = $nextDuration;
        $session->started_at       = now();
        $session->paused_at        = null;
        $session->save();

        // Засчитываем помидорку всем участникам в фокусе
        if ($nextPhase === 'break') {
            $this->workspace->members()
                ->where('status', 'focus')
                ->increment('pomodoros_today');
        } else {
            // Перерыв закончился — все автоматически в «фокус»
            $this->workspace->members()->update(['status' => 'focus', 'status_updated_at' => now()]);
        }

        broadcast(new WorkspaceTimerUpdated($this->workspace->id, $session->fresh(), 'finished'));

        $label = $nextPhase === 'break' ? 'Перерыв!' : 'Снова в работу!';
        $this->dispatch('toast', type: 'success', title: $label);
    }

    // ===== Чат =====

    public function sendMessage(): void
    {
        $body = trim($this->chatInput);
        if ($body === '' || mb_strlen($body) > 500) return;

        $message = WorkspaceMessage::create([
            'workspace_id' => $this->workspace->id,
            'user_id'      => Auth::id(),
            'body'         => $body,
        ]);

        $message->load('user');

        $this->chatInput = '';

        broadcast(new WorkspaceChatMessageSent($message))->toOthers();
    }

    // ===== Реакции =====

    public function sendReaction(string $emoji, ?int $toUserId = null): void
    {
        abort_unless(in_array($emoji, ['👏', '🧘', '☕', '🔥', '💪'], true), 422);

        $reaction = WorkspaceReaction::create([
            'workspace_id' => $this->workspace->id,
            'from_user_id' => Auth::id(),
            'to_user_id'   => $toUserId,
            'emoji'        => $emoji,
        ]);

        $reaction->load('fromUser', 'toUser');

        // Чистим старые реакции (старше 30 сек) чтобы таблица не пухла
        WorkspaceReaction::where('workspace_id', $this->workspace->id)
            ->where('created_at', '<', now()->subSeconds(30))
            ->delete();

        broadcast(new WorkspaceReactionSent($reaction)); // без toOthers — отправитель тоже видит свою реакцию
    }

    // ===== Управление комнатой =====

    public function leaveRoom(): void
    {
        $member = $this->myMember();

        if ($this->workspace->owner_id === Auth::id()) {
            // Владелец передаёт права следующему или удаляет комнату
            $next = $this->workspace->members()
                ->where('user_id', '!=', Auth::id())
                ->first();

            if ($next) {
                $this->workspace->update(['owner_id' => $next->user_id]);
                $next->update(['role' => 'owner']);
            } else {
                $this->workspace->delete();
                $this->redirect(route('app'));
                return;
            }
        }

        $member->delete();

        $this->redirect(route('workspace.index'));
    }

    // ===== Helpers =====

    protected function myMember(): WorkspaceMember
    {
        return WorkspaceMember::where('workspace_id', $this->workspace->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    protected function touchMember(): void
    {
        WorkspaceMember::where('workspace_id', $this->workspace->id)
            ->where('user_id', Auth::id())
            ->update(['last_seen_at' => now()]);
    }
}
