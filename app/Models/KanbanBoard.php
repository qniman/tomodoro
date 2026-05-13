<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    protected $fillable = ['user_id', 'name', 'color', 'position'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(KanbanColumn::class, 'board_id')->orderBy('position');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'kanban_board_members', 'board_id', 'user_id')
            ->withTimestamps();
    }

    public function canAccess(int $userId): bool
    {
        return $this->user_id === $userId
            || $this->members()->where('user_id', $userId)->exists();
    }
}
