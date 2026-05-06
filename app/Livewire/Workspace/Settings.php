<?php

namespace App\Livewire\Workspace;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Настройки')]
class Settings extends Component
{
    use WithFileUploads;

    #[Url]
    public string $tab = 'profile';

    /* ===== Профиль ===== */
    public string $name = '';
    public string $email = '';
    public ?string $avatarPath = null;
    public $newAvatar = null;

    /* ===== Безопасность ===== */
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    /* ===== Внешний вид ===== */
    public string $theme = 'auto';

    /* ===== Помодоро ===== */
    public int $workMinutes = 25;
    public int $shortBreakMinutes = 5;
    public int $longBreakMinutes = 15;
    public int $longBreakEvery = 4;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->avatarPath = $user->avatar_path;
        $this->theme = $user->theme ?? 'auto';

        $prefs = $user->pomodoro_preferences;
        $this->workMinutes = (int) ($prefs['work_minutes'] ?? 25);
        $this->shortBreakMinutes = (int) ($prefs['short_break_minutes'] ?? 5);
        $this->longBreakMinutes = (int) ($prefs['long_break_minutes'] ?? 15);
        $this->longBreakEvery = (int) ($prefs['long_break_every'] ?? 4);
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['profile', 'security', 'appearance', 'pomodoro', 'shortcuts'], true) ? $tab : 'profile';
    }

    /* ============================================================ *
     *  Профиль
     * ============================================================ */

    public function saveProfile(): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name' => trim($this->name),
            'email' => trim($this->email),
        ]);

        $this->dispatch('toast', type: 'success', title: 'Профиль обновлён');
    }

    public function uploadAvatar(): void
    {
        $this->validate([
            'newAvatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        if ($this->avatarPath && Storage::disk('public')->exists($this->avatarPath)) {
            Storage::disk('public')->delete($this->avatarPath);
        }

        $path = $this->newAvatar->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);
        $this->avatarPath = $path;
        $this->newAvatar = null;

        $this->dispatch('toast', type: 'success', title: 'Аватар обновлён');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($this->avatarPath && Storage::disk('public')->exists($this->avatarPath)) {
            Storage::disk('public')->delete($this->avatarPath);
        }

        $user->update(['avatar_path' => null]);
        $this->avatarPath = null;
        $this->newAvatar = null;

        $this->dispatch('toast', type: 'info', title: 'Аватар удалён');
    }

    /* ============================================================ *
     *  Безопасность
     * ============================================================ */

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed:newPasswordConfirmation'],
        ], attributes: [
            'currentPassword' => 'текущий пароль',
            'newPassword' => 'новый пароль',
        ]);

        Auth::user()->update(['password' => Hash::make($this->newPassword)]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->dispatch('toast', type: 'success', title: 'Пароль обновлён');
    }

    /* ============================================================ *
     *  Внешний вид
     * ============================================================ */

    public function setTheme(string $theme): void
    {
        $theme = in_array($theme, ['light', 'dark', 'auto'], true) ? $theme : 'auto';
        Auth::user()->update(['theme' => $theme]);
        $this->theme = $theme;

        // Браузерное событие — JS-слушатель применит тему мгновенно.
        $this->dispatch('apply-theme', theme: $theme);
        $this->dispatch('toast', type: 'success', title: 'Тема изменена');
    }

    /* ============================================================ *
     *  Помодоро
     * ============================================================ */

    public function savePomodoro(): void
    {
        $this->validate([
            'workMinutes' => ['required', 'integer', 'min:5', 'max:120'],
            'shortBreakMinutes' => ['required', 'integer', 'min:1', 'max:60'],
            'longBreakMinutes' => ['required', 'integer', 'min:1', 'max:90'],
            'longBreakEvery' => ['required', 'integer', 'min:2', 'max:12'],
        ]);

        $prefs = [
            'work_minutes' => $this->workMinutes,
            'short_break_minutes' => $this->shortBreakMinutes,
            'long_break_minutes' => $this->longBreakMinutes,
            'long_break_every' => $this->longBreakEvery,
        ];

        Auth::user()->update(['pomodoro_settings' => $prefs]);

        $this->dispatch('toast', type: 'success', title: 'Настройки помодоро сохранены');
    }

    public function resetPomodoro(): void
    {
        Auth::user()->update(['pomodoro_settings' => null]);
        $defaults = User::DEFAULT_POMODORO_SETTINGS;
        $this->workMinutes = (int) $defaults['work_minutes'];
        $this->shortBreakMinutes = (int) $defaults['short_break_minutes'];
        $this->longBreakMinutes = (int) $defaults['long_break_minutes'];
        $this->longBreakEvery = (int) $defaults['long_break_every'];

        $this->dispatch('toast', type: 'info', title: 'Настройки сброшены к умолчанию');
    }

    public function render()
    {
        return view('livewire.workspace.settings', [
            'user' => Auth::user(),
        ]);
    }
}
