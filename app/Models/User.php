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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'username',
        'slug',
        'email',
        'phone',
        'password',
        'biography',
        'jourcy_number',

        'otp',
        'otp_expires_at',
        'otp_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',

        'avatar',
        'last_activity_at',

        'stripe_customer_id',
        'stripe_account_id',

        'status'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'otp_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity_at' => 'datetime'
        ];
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


    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    // User Model
    public function evaluatorEvaluations()
    {
        return $this->hasMany(RefereeEvaluation::class, 'evaluator_id');
    }

    // camp checkin referee
    public function refereeCheckins()
    {
        return $this->hasMany(CampRefereeCheckin::class, 'referee_id');
    }

    // referee evaluation
    public function evaluations()
    {
        return $this->hasMany(RefereeEvaluation::class, 'referee_id');
    }

    /**
     * Get unread announcements count
     */
    public function unreadAnnouncementsCount()
    {
        return $this->unreadNotifications()
            ->where('type', 'AnnouncementNotification')
            ->count();
    }

    /**
     * Referee camps with jersey numbers
     */
    public function refereeCamps()
    {
        return $this->belongsToMany(
            Camp::class,
            'camp_referee_jearsy_numbers',
            'referee_id',
            'camp_id'
        )->withPivot('jersey_number')
            ->withTimestamps();
    }
}
