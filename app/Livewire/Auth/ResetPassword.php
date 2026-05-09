<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Новый пароль')]
class ResetPassword extends Component
{
    public string $token = '';
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    #[Validate('required|string|min:8')]
    public string $password_confirmation = '';

    public bool $done = false;
    public ?string $error = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function submit(): void
    {
        $this->validate();
        $this->error = null;

        $status = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                Auth::login($user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->done = true;
            $this->redirect(route('app'), navigate: false);
        } else {
            $this->error = match ($status) {
                Password::INVALID_TOKEN => 'Ссылка устарела или недействительна. Запросите новую.',
                Password::INVALID_USER  => 'Пользователь с таким email не найден.',
                default                 => 'Не удалось сбросить пароль. Попробуйте ещё раз.',
            };
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
