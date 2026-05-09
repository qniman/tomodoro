<?php

namespace App\Models;

use App\Mail\PasswordResetMail;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'vk_id',
        'avatar_path',
        'theme',
        'pomodoro_settings',
        'password_is_placeholder',
        'last_seen_changelog_version',
        'hide_changelog_modal',
        'email_verification_code',
        'email_verification_expires_at',
        'pending_email',
        'pending_email_code',
        'pending_email_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'              => 'datetime',
        'email_verification_expires_at'  => 'datetime',
        'pending_email_expires_at'       => 'datetime',
        'password'                       => 'hashed',
        'password_is_placeholder'        => 'boolean',
        'pomodoro_settings'              => 'array',
        'hide_changelog_modal'           => 'boolean',
    ];

    public const DEFAULT_POMODORO_SETTINGS = [
        'work_minutes' => 25,
        'short_break_minutes' => 5,
        'long_break_minutes' => 15,
        'long_break_every' => 4,
        'auto_start_break' => true,
        'auto_start_work' => false,
        'sound' => true,
    ];

    public function getPomodoroPreferencesAttribute(): array
    {
        return array_replace(self::DEFAULT_POMODORO_SETTINGS, $this->pomodoro_settings ?? []);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null;
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name ?? ''));
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            if ($part === '') {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials ?: '·';
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function pomodoroSessions(): HasMany
    {
        return $this->hasMany(PomodoroSession::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function sendPasswordResetNotification($token): void
    {
        Mail::to($this->email)->send(new PasswordResetMail($this, $token));
    }
}
