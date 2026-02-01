<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Model;
=======
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;
>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182

class Profile extends Model
{
    protected $fillable = [
        'user_id',
<<<<<<< HEAD
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
=======
        'username',
        'image',
        'tag_line',
        'description',
    ];

>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
    public function user()
    {
        return $this->belongsTo(User::class);
    }
<<<<<<< HEAD
=======

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'profiles_languages')
            ->withPivot(['proficiency_level'])
            ->withTimestamps();
    }
>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
}
