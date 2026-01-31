<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'color',
    ];

    public const ALLOWED_STATUSES = [
        ['name' => 'Новое', 'color' => '#facc15'],
        ['name' => 'В работе', 'color' => '#38bdf8'],
        ['name' => 'Завершено', 'color' => '#34d399'],
        ['name' => 'Отложено', 'color' => '#f97316'],
        ['name' => 'Просрочено', 'color' => '#ef4444'],
    ];

    public static function allowedNames(): array
    {
        return array_map(fn ($s) => $s['name'], self::ALLOWED_STATUSES);
    }

    public static function forUserAllowed(int $userId)
    {
        return self::where('user_id', $userId)
            ->whereIn('name', self::allowedNames())
            ->orderBy('name')
            ->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
