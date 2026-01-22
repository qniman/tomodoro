<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'work_sec',
        'break_sec',
        'total_pomodoros',
        'completed_pomodoros',
        'status',
        'phase',
        'started_at',
        'ended_at',
        'synced_seconds',
        'paused_at',
        'phase_started_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'paused_at' => 'datetime',
        'phase_started_at' => 'datetime',
        'synced_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function isInBreak(): bool
    {
        return $this->phase === 'break';
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function getRemainingSeconds(): int
    {
        $maxSec = $this->isInBreak() ? $this->break_sec : $this->work_sec;
        $phaseStart = $this->phase_started_at ? Carbon::parse($this->phase_started_at) : null;
        if (! $phaseStart) {
            return $maxSec;
        }
        $elapsed = Carbon::now()->diffInSeconds($phaseStart, false);
        return max(0, $maxSec - $elapsed);
    }
}
