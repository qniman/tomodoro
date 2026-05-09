<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReleaseNotesModal extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        $current = (string) config('changelog.current', '');

        if ($current === '' || ! Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User || $user->hide_changelog_modal) {
            return;
        }

        if ($this->userNeedsAck($user, $current)) {
            $this->open = true;
        }
    }

    public function dismiss(): void
    {
        $current = (string) config('changelog.current', '');

        if ($current !== '' && Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User) {
                $user->update(['last_seen_changelog_version' => $current]);
            }
        }

        $this->open = false;
    }

    protected function userNeedsAck(User $user, string $currentVersion): bool
    {
        $last = $user->last_seen_changelog_version;

        return $last === null || $last === '' || version_compare($last, $currentVersion, '<');
    }

    public function render()
    {
        $lastSeen = '';
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User) {
                $lastSeen = (string) ($user->last_seen_changelog_version ?? '');
            }
        }

        $releases = array_map(
            static fn (array $r) => array_merge($r, [
                'is_new' => $lastSeen === '' || version_compare($r['version'], $lastSeen, '>'),
            ]),
            (array) config('changelog.releases', [])
        );

        return view('livewire.release-notes-modal', compact('releases'));
    }
}
