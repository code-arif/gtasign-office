<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Profile;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'web';

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
        'phone',
        'password',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_agreed' => 'boolean'
    ];


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

    /**
     * Relationship: Educations Table
     */
    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Relationship: Certifications Table
     */
    public function certifications()
    {
        return $this->hasMany(Certification::class);
    }

    /**
     * Relationship: Experiences Table
     */
    public function experiences()
    {
        return $this->hasMany(UserExperience::class);
    }

    /**
     * Relationship: Language table
     */
    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_languages')
            ->withPivot('proficiency')
            ->withTimestamps();
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // You can also add inactive/suspended if needed
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // Relationships
    public function gigs()
    {
        return $this->hasMany(Gig::class, 'user_id');
    }

    public function sellerOrders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function buyerOrders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }
}
