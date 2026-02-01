<?php

namespace Modules\Director\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\SportsType;
use App\Models\RefereeEvaluation;
use Illuminate\Database\Eloquent\Model;
use App\Models\CampEvaluatorRegistration;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\HandlesTimezones;

class Camp extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date:Y-m-d',  // Force Y-m-d format
        'end_date' => 'date:Y-m-d',    // Force Y-m-d format
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'timezone_display_name',
        'timezone_offset_hours',
    ];

    /**
     * Get the director that owns the camp
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get the sports type
     */
    public function sportsType(): BelongsTo
    {
        return $this->belongsTo(SportsType::class);
    }

    /**
     * Get the camp's schedule
     */
    public function schedule(): HasOne
    {
        return $this->hasOne(Schedule::class);
    }

    /**
     * Get checked-in referees
     */
    public function checkedInReferees(): HasMany
    {
        return $this->hasMany(CampRefereeCheckin::class);
    }

    /**
     * Get camp logo URL
     */
    public function getCampLogoUrlAttribute()
    {
        return $this->camp_logo
            ? asset(' ' . $this->camp_logo)
            : null;
    }

    /**
     * Check if camp is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if camp has started
     */
    public function hasStarted(): bool
    {
        return now()->gte($this->start_date);
    }

    /**
     * Check if camp has ended
     */
    public function hasEnded(): bool
    {
        return now()->gt($this->end_date);
    }

    /**
     * Get camp duration in days
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }


    // Get all evaluators who evaluated referees in this camp
    public function evaluations()
    {
        return $this->hasMany(RefereeEvaluation::class, 'camp_id');
    }

    // Get unique evaluators who evaluated in this camp
    public function getEvaluatorsAttribute()
    {
        return User::whereIn('id', function ($query) {
            $query->select('evaluator_id')
                ->from('referee_evaluations')
                ->where('camp_id', $this->id)
                ->distinct();
        })->get();
    }

    // Camp.php model এ
    public function evaluatorRegistrations()
    {
        return $this->hasMany(CampEvaluatorRegistration::class, 'camp_id');
    }

    /**
     * Accessors
     */
    public function getTimezoneDisplayNameAttribute(): string
    {
        return HandlesTimezones::getDisplayName($this->timezone ?? 'UTC');
    }

    public function getTimezoneOffsetHoursAttribute(): float
    {
        return Carbon::now($this->timezone ?? 'UTC')->offsetHours;
    }

    /**
     * Helper method to convert time to camp timezone
     */
    public function toCampTime($dateTime, string $format = 'Y-m-d H:i:s'): string
    {
        return HandlesTimezones::convertToTimezone($dateTime, $this->timezone, $format);
    }

    /**
     * Get timezone offset message for users
     */
    public function getTimezoneOffsetText(?string $userTimezone = null): ?string
    {
        return HandlesTimezones::getOffsetText($this->timezone, $userTimezone);
    }

    /**
     * Boot method to auto-detect timezone
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($camp) {
            // Auto-detect timezone if not provided but coordinates exist
            if (!$camp->timezone && $camp->latitude && $camp->longitude) {
                $camp->timezone = HandlesTimezones::detectFromCoordinates(
                    $camp->latitude,
                    $camp->longitude
                );
            }

            // Default to UTC if still not set
            if (!$camp->timezone) {
                $camp->timezone = config('app.timezone', 'UTC');
            }
        });
    }

    /**
     * Relation with camp table
     */
    public function crews()
    {
        return $this->hasMany(Crew::class);
    }

    /**
     * Relation with jersey numbers
     */
    public function referees()
    {
        return $this->belongsToMany(
            User::class,
            'camp_referee_jearsy_numbers',
            'camp_id',
            'referee_id'
        )->withPivot('jersey_number')
            ->withTimestamps();
    }
}
