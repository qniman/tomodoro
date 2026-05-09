<?php

namespace App\Livewire\Auth;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Сброс пароля')]
class ForgotPassword extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $sent = false;

    public function submit(): void
    {
        $this->validate();

        // Always send success message (security — don't reveal if email exists)
        $user = User::where('email', $this->email)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            Mail::to($user->email)->send(new PasswordResetMail($user, $token));
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
