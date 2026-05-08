<?php

namespace Tests\Feature;

use App\Livewire\ReleaseNotesModal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class ReleaseNotesModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_opens_when_user_never_acknowledged(): void
    {
        Config::set('changelog.version', '1.0.0');

        $user = User::factory()->create([
            'last_seen_changelog_version' => null,
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', true);
    }

    public function test_modal_opens_when_user_changelog_version_is_behind(): void
    {
        Config::set('changelog.version', '2.0.0');
        Config::set('changelog.title', 'Test title');
        Config::set('changelog.items', ['Line one']);

        $user = User::factory()->create([
            'last_seen_changelog_version' => '1.0.0',
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', true)
            ->assertSet('title', 'Test title');
    }

    public function test_modal_closed_when_user_already_acknowledged_current_version(): void
    {
        Config::set('changelog.version', '2.0.0');

        $user = User::factory()->create([
            'last_seen_changelog_version' => '2.0.0',
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', false);
    }

    public function test_dismiss_updates_user_and_closes_modal(): void
    {
        Config::set('changelog.version', '3.1.0');

        $user = User::factory()->create([
            'last_seen_changelog_version' => '1.0.0',
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', true)
            ->call('dismiss')
            ->assertSet('open', false);

        $user->refresh();
        $this->assertSame('3.1.0', $user->last_seen_changelog_version);
    }

    public function test_modal_not_shown_when_user_opted_out(): void
    {
        Config::set('changelog.version', '2.0.0');

        $user = User::factory()->create([
            'last_seen_changelog_version' => '1.0.0',
            'hide_changelog_modal' => true,
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', false);
    }

    public function test_modal_not_shown_when_version_config_empty(): void
    {
        Config::set('changelog.version', '');

        $user = User::factory()->create([
            'last_seen_changelog_version' => null,
        ]);

        Livewire::actingAs($user)
            ->test(ReleaseNotesModal::class)
            ->assertSet('open', false);
    }
}
