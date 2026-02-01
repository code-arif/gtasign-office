<?php

namespace Modules\Director\Models;

use App\Models\CampPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Director\Database\Factories\CampRefereeCheckinFactory;

class CampRefereeCheckin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'registered_at' => 'datetime',
        'checked_in_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    // relation to payment
    public function payment()
    {
        return $this->hasOneThrough(
            CampPayment::class,
            Camp::class,
            'id', // Foreign key on camps table
            'camp_id', // Foreign key on camp_payments table
            'camp_id', // Local key on camp_referee_checkins table
            'id' // Local key on camps table
        )->where('camp_payments.referee_id', $this->referee_id);
    }

    // Scopes
    public function scopeRegistered($query)
    {
        return $query->where('registration_status', 'registered');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('registration_status', 'checked_in');
    }

    // Helper methods
    public function isRegistered(): bool
    {
        return $this->registration_status === 'registered';
    }

    public function isCheckedIn(): bool
    {
        return $this->registration_status === 'checked_in';
    }

    public function canCheckIn(Camp $camp): bool
    {
        // Already checked in
        if ($this->isCheckedIn()) {
            return false;
        }

        // Must be registered first
        if (!$this->isRegistered()) {
            return false;
        }

        // Camp must have started
        $today = now()->toDateString();
        return $camp->start_date <= $today && $camp->end_date >= $today;
    }
}
