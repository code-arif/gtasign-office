<?php

namespace Modules\Director\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSlot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'game_date' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(ScheduleLocation::class, 'schedule_location_id');
    }

    /**
     * New polymorphic assignments relationship
     */
    public function slotAssignments(): HasMany
    {
        return $this->hasMany(GameSlotAssignment::class);
    }

    /**
     * Legacy relationship - keep for backward compatibility if needed
     */
    public function refereeAssignments(): HasMany
    {
        return $this->hasMany(GameSlotAssignment::class);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Check if slot has crew assignment
     */
    public function hasCrewAssignment(): bool
    {
        return $this->slotAssignments()
            ->where('assignment_type', 'crew')
            ->exists();
    }

    /**
     * Check if slot has individual assignments
     */
    public function hasIndividualAssignments(): bool
    {
        return $this->slotAssignments()
            ->where('assignment_type', 'individual')
            ->exists();
    }

    /**
     * Get total referee count (crew members + individuals)
     */
    public function getTotalRefereesAttribute(): int
    {
        $crewAssignment = $this->slotAssignments()
            ->where('assignment_type', 'crew')
            ->with('assignable.members')
            ->first();

        if ($crewAssignment) {
            return $crewAssignment->assignable->members->count();
        }

        return $this->slotAssignments()
            ->where('assignment_type', 'individual')
            ->count();
    }

    /**
     * Check if slot is full (has 3 referees or crew)
     */
    public function isFull(): bool
    {
        if ($this->hasCrewAssignment()) {
            return true; // Crew takes full slot
        }

        return $this->slotAssignments()
            ->where('assignment_type', 'individual')
            ->count() >= 3;
    }

    /**
     * Get available slots for individual assignment
     */
    public function availableSlots(): int
    {
        if ($this->hasCrewAssignment()) {
            return 0;
        }

        $currentCount = $this->slotAssignments()
            ->where('assignment_type', 'individual')
            ->count();

        return max(0, 3 - $currentCount);
    }

    /**
     * Get all assignments for this game slot (crew or individual)
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(GameSlotAssignment::class, 'game_slot_id');
    }
}
