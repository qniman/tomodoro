<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSession extends Model
{
    protected $fillable = [
        'workspace_id', 'started_by',
        'phase', 'duration_seconds',
        'started_at', 'paused_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at'  => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function isActive(): bool
    {
        return $this->paused_at === null && $this->remainingSeconds() > 0;
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function remainingSeconds(): int
    {
        if ($this->paused_at) {
            $elapsed = $this->started_at->diffInSeconds($this->paused_at);
        } else {
            $elapsed = $this->started_at->diffInSeconds(now());
        }

        return max(0, $this->duration_seconds - (int) $elapsed);
    }

    public function phaseLabel(): string
    {
        return $this->phase === 'work' ? 'Фокус' : 'Перерыв';
    }
}
