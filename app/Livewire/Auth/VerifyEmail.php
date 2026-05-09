<?php

namespace App\Livewire\Auth;

use App\Mail\VerifyCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Подтверждение email')]
class VerifyEmail extends Component
{
    public string $code = '';
    public bool $sent = false;
    public ?string $error = null;

    public function mount(): void
    {
        if (Auth::user()?->email_verified_at) {
            $this->redirect(route('app'), navigate: true);
        }
    }

    public function submit(): void
    {
        $this->error = null;
        /** @var User $user */
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $inputCode = trim($this->code);

        if (
            ! $user->email_verification_code ||
            ! $user->email_verification_expires_at ||
            $user->email_verification_expires_at->isPast()
        ) {
            $this->error = 'Код устарел или недействителен. Запросите новый.';
            return;
        }

        if ($user->email_verification_code !== $inputCode) {
            $this->error = 'Неверный код. Проверьте письмо и попробуйте снова.';
            return;
        }

        $user->email_verified_at       = now();
        $user->email_verification_code = null;
        $user->email_verification_expires_at = null;
        $user->save();

        $this->dispatch('toast',
            type: 'success',
            title: 'Email подтверждён!',
            message: 'Теперь вы получаете уведомления.',
        );

        $this->redirect(route('app'), navigate: false);
    }

    public function resend(): void
    {
        /** @var User $user */
        $user = Auth::user();
        if (! $user || $user->email_verified_at) {
            return;
        }

        $code = $this->generateAndStoreCode($user);
        Mail::to($user->email)->send(new VerifyCodeMail($user, $code));

        $this->sent = true;
        $this->error = null;

        $this->dispatch('toast',
            type: 'success',
            title: 'Код отправлен',
            message: 'Проверьте почту ' . $user->email,
        );
    }

    public static function generateAndStoreCode(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->email_verification_code        = $code;
        $user->email_verification_expires_at  = Carbon::now()->addMinutes(30);
        $user->save();

        return $code;
    }

    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
