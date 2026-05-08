<?php

namespace App\Livewire\Workspace;

use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Комнаты')]
class RoomIndex extends Component
{
    public bool $showCreateModal = false;

    #[Validate('required|string|min:2|max:80')]
    public string $newRoomName = '';

    public string $joinCode = '';

    public function mount(): void
    {
        $code = strtoupper(trim(request()->query('join', '')));
        if ($code !== '') {
            $this->joinCode = $code;
            $this->joinRoom();
        }
    }

    public function render()
    {
        $myRooms = WorkspaceMember::where('user_id', Auth::id())
            ->with(['workspace.members.user', 'workspace.activeSession'])
            ->get()
            ->pluck('workspace')
            ->filter()
            ->sortByDesc('updated_at');

        return view('livewire.workspace.room-index', ['myRooms' => $myRooms]);
    }

    public function createRoom(): void
    {
        $this->validate();

        $workspace = Workspace::create([
            'owner_id'    => Auth::id(),
            'name'        => trim($this->newRoomName),
            'invite_code' => Workspace::generateCode(),
        ]);

        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'user_id'      => Auth::id(),
            'role'         => 'owner',
            'status'       => 'away',
        ]);

        $this->showCreateModal = false;
        $this->newRoomName = '';

        $this->redirect(route('workspace.room', $workspace));
    }

    public function joinRoom(): void
    {
        $code = strtoupper(trim($this->joinCode));

        $workspace = Workspace::where('invite_code', $code)->where('is_active', true)->first();

        if (! $workspace) {
            $this->addError('joinCode', 'Комната не найдена. Проверьте код.');
            return;
        }

        WorkspaceMember::firstOrCreate(
            ['workspace_id' => $workspace->id, 'user_id' => Auth::id()],
            ['role' => 'member', 'status' => 'away'],
        );

        $this->joinCode = '';
        $this->redirect(route('workspace.room', $workspace));
    }
}
