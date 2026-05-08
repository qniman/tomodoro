<?php

namespace Tests\Feature;

use App\Livewire\Workspace\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class OauthAccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_placeholder_user_can_set_password_in_settings(): void
    {
        $user = User::factory()->oauthRandomPassword()->create();

        Livewire::actingAs($user)
            ->test(Settings::class)
            ->set('tab', 'security')
            ->set('newPassword', 'new-password-8')
            ->set('newPasswordConfirmation', 'new-password-8')
            ->call('setExternalLoginPassword')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertFalse($user->password_is_placeholder);
        $this->assertTrue(Hash::check('new-password-8', $user->password));
    }
}
