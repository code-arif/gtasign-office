<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'slug',
        'tagline',
        'biography',
        'address',
        'avatar',
        'is_pinned',
        'level',
        'level_name',
        'stripe_account_id',
        'stripe_onboarded_at',
        'last_active_at',
        'is_online'
    ];

    /**
     * Relationship: user table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
