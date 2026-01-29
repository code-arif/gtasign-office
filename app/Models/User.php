<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, Billable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'is_verified',
        'email_verified_at',
        'password',
        'role',
        'group',
        'status',
    ];


    protected $hidden = [
        'password',
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

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }








    public function getAvatarAttribute($value): string | null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        if (request()->is('api/*') && ! empty($value)) {

            return url($value);
        }
        return $value;
    }

    // company
    public function company()
    {
        return $this->hasOne(Company::class, 'user_id', 'id');
    }

    // employee
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }
    public function certifcations()
    {
        return $this->hasMany(EmployeeCertification::class, 'employee_id', 'id');
    }

    public function experiences()
    {
        return $this->hasMany(EmployeeExperience::class, 'employee_id', 'id');
    }
    public function job_categories()
    {
        return $this->hasMany(EmployeeJobCategory::class, 'employee_id', 'id');
    }
    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class, 'employee_id', 'id');
    }
    public function specializes()
    {
        return $this->hasMany(EmployeeSpecialize::class, 'employee_id', 'id');
    }
    public function specializations()
    {
        return $this->hasMany(CompanySpecialize::class, 'company_id', 'id');
    }
    public function get_project()
    {
        return $this->hasMany(CompanyProject::class, 'company_id', 'id');
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
