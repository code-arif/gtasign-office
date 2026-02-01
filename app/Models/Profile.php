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
    ];

    /**
     * Relationship: user table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
