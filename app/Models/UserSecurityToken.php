<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class UserSecurityToken extends Model
{
    protected $table = 'user_security_tokens';

    protected $fillable = [
        'user_id',
        'identifier',
        'token_hash',
        'type',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    /* =========================
     * Relationships
     * ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* =========================
     * Scopes
     * ========================= */

    public function scopeValid(Builder $query)
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    /* =========================
     * Helpers
     * ========================= */

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function markAsUsed(): void
    {
        $this->update([
            'used_at' => now(),
        ]);
    }
}
