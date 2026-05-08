<?php

namespace App\Models;

use App\Support\UiIconSet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'icon',
        'position',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    /** @return array<string, string> */
    public static function iconChoices(): array
    {
        return UiIconSet::choices();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /** @param  Builder<Project>  $query */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /** @param  Builder<Project>  $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    /** @param  Builder<Project>  $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }

    public function displayIcon(): string
    {
        $icon = $this->icon;
        if ($icon && array_key_exists($icon, self::iconChoices())) {
            return $icon;
        }

        return 'folder';
    }
}
