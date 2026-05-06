<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    protected $fillable = [
        'user_id',
        'project_id',
        'parent_id',
        'title',
        'description_html',
        'description_text',
        'priority',
        'due_at',
        'reminder_at',
        'all_day',
        'completed_at',
        'is_pinned',
        'estimated_minutes',
        'spent_seconds',
        'completed_pomodoros',
        'position',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'all_day' => 'boolean',
        'is_pinned' => 'boolean',
        'estimated_minutes' => 'integer',
        'spent_seconds' => 'integer',
        'completed_pomodoros' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Task $task) {
            if ($task->isDirty('description_html')) {
                $task->description_text = trim(html_entity_decode(strip_tags((string) $task->description_html)));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'task_tag');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(TaskChecklistItem::class)->orderBy('position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    public function pomodoroSessions(): HasMany
    {
        return $this->hasMany(PomodoroSession::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && ! $this->isCompleted()
            && $this->due_at->isPast();
    }
}
