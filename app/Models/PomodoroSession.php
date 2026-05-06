<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroSession extends Model
{
    use HasFactory;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ABORTED = 'aborted';

    public const PHASE_WORK = 'work';
    public const PHASE_SHORT_BREAK = 'short_break';
    public const PHASE_LONG_BREAK = 'long_break';

    protected $fillable = [
        'user_id',
        'task_id',
        'status',
        'phase',
        'work_seconds',
        'short_break_seconds',
        'long_break_seconds',
        'long_break_every',
        'total_pomodoros',
        'completed_pomodoros',
        'phase_started_at',
        'paused_at',
        'synced_seconds',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'phase_started_at' => 'datetime',
        'paused_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function isWorking(): bool
    {
        return $this->phase === self::PHASE_WORK;
    }

    public function phaseDuration(): int
    {
        return match ($this->phase) {
            self::PHASE_SHORT_BREAK => (int) $this->short_break_seconds,
            self::PHASE_LONG_BREAK => (int) $this->long_break_seconds,
            default => (int) $this->work_seconds,
        };
    }

    public function remainingSeconds(): int
    {
        $duration = $this->phaseDuration();
        if (! $this->phase_started_at) {
            return $duration;
        }

        if ($this->paused_at) {
            $elapsed = $this->paused_at->diffInSeconds($this->phase_started_at);
        } else {
            $elapsed = Carbon::now()->diffInSeconds($this->phase_started_at, false);
        }

        return max(0, $duration - max(0, (int) $elapsed));
    }
}
