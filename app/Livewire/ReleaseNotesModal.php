<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReleaseNotesModal extends Component
{
    public bool $open = false;

    public string $title = '';

    public string $subtitle = '';

    /** @var array<int, string> */
    public array $items = [];

    public function mount(): void
    {
        $current = (string) config('changelog.version', '');

        if ($current === '' || ! Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }

        if ($user->hide_changelog_modal) {
            return;
        }

        if (! $this->userNeedsAck($user, $current)) {
            return;
        }

        $this->title = (string) config('changelog.title', '');
        $this->subtitle = (string) config('changelog.subtitle', '');
        $this->items = array_values(array_filter(
            (array) config('changelog.items', []),
            static fn ($line) => is_string($line) && $line !== ''
        ));
        $this->open = true;
    }

    public function dismiss(): void
    {
        $current = (string) config('changelog.version', '');

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

        if ($last === null || $last === '') {
            return true;
        }

        return version_compare($last, $currentVersion, '<');
    }

    public function render()
    {
        return view('livewire.release-notes-modal');
    }
}
