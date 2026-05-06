<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Вход')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:1')]
    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectIntended(route('app'));
        }
    }

    public function submit(): void
    {
        $credentials = $this->validate();

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $this->remember
        )) {
            $this->addError('email', 'Не удалось войти. Проверьте email и пароль.');

            return;
        }

        request()->session()->regenerate();

        $this->redirectIntended(route('app'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
