<?php

namespace App\Livewire\Workspace;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SidebarTags extends Component
{
    #[On('tags-updated')]
    public function onTagsUpdated(): void
    {
        //
    }

    public function render()
    {
        $query = Tag::query()
            ->forUser(Auth::id())
            ->ordered();

        $raw = request()->query('tag');
        $activeTagId = is_numeric($raw) ? (int) $raw : null;

        return view('livewire.workspace.sidebar-tags', [
            'tags' => $query->get(),
            'activeTagId' => $activeTagId,
        ]);
    }
}
