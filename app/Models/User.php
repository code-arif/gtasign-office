<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Stripe\Plan;
use Stripe\Product;
use Modules\Director\Models\Camp;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\AnnouncementNotification;
use Modules\Director\Models\CampRefereeCheckin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = ['api', 'web'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'email',
<<<<<<< HEAD
        'phone',
        'password',
        'status',
        'email_verified_at',
=======
        'is_verified',
        'email_verified_at',
        'password',
        'role',
        'group',
        'status',
>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182
    ];

    protected $hidden = [
        'password',
<<<<<<< HEAD
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

=======
        // 'remember_token',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'               => 'datetime',
            // 'otp_expires_at'                  => 'datetime',
            // 'is_otp_verified'                 => 'boolean',
            // 'reset_password_token_expires_at' => 'datetime',
            'password'                        => 'hashed',
        ];
    }
>>>>>>> 7ad60917571d7a5f37e452e0a0d43bef9b511182

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }








    public function getAvatarAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    public function getRoleAttribute()
    {
        return  $this->getRoleNames()->first();
    }

    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseTokens::class);
    }


    /**
     * Relationship: Profile table
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }
    public function latestOtp()
    {
        return $this->hasOne(OtpVerification::class)->latestOfMany();
    }
}
