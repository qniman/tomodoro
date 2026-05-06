<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function getUrlAttribute(): ?string
    {
        try {
            return Storage::disk($this->disk)->url($this->path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1).' МБ';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' КБ';
        }

        return $bytes.' Б';
    }
}
