<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    protected $fillable = ['board_id', 'name', 'color', 'position'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'kanban_column_id')->orderBy('kanban_position');
    }
}
