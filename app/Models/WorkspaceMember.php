<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMember extends Model
{
    protected $fillable = [
        'workspace_id', 'user_id', 'role',
        'status', 'status_updated_at',
        'pomodoros_today', 'last_seen_at',
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
        'last_seen_at'      => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isFocused(): bool
    {
        return $this->status === 'focus';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'focus' => 'В фокусе',
            'pause' => 'На паузе',
            default => 'Отошёл',
        };
    }
}
