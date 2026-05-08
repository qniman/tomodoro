<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Workspace extends Model
{
    protected $fillable = ['owner_id', 'name', 'invite_code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('invite_code', $code)->exists());

        return $code;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(WorkspaceSession::class)->latestOfMany();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WorkspaceMessage::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(WorkspaceReaction::class);
    }

    public function isMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
