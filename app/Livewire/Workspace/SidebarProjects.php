<?php

namespace App\Livewire\Workspace;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarProjects extends Component
{
    #[On('projects-updated')]
    public function onProjectsUpdated(): void
    {
        //
    }

    public function render()
    {
        $query = Project::query()
            ->forUser(Auth::id())
            ->active()
            ->ordered();

        $raw = request()->query('project');
        $activeProjectId = is_numeric($raw) ? (int) $raw : null;

        return view('livewire.workspace.sidebar-projects', [
            'projects' => $query->get(),
            'activeProjectId' => $activeProjectId,
        ]);
    }
}
