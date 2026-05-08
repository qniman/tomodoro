<?php

namespace App\Models;

use App\Support\UiIconSet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'color', 'icon'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_tag');
    }

    /** @return array<string, string> */
    public static function iconChoices(): array
    {
        return UiIconSet::choices();
    }

    /** @param  Builder<Tag>  $query */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /** @param  Builder<Tag>  $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    public function displayIcon(): string
    {
        $icon = $this->icon;
        if ($icon && array_key_exists($icon, self::iconChoices())) {
            return $icon;
        }

        return 'tag';
    }
}
