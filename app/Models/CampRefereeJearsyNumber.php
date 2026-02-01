<?php

namespace App\Models;

use Modules\Director\Models\Camp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampRefereeJearsyNumber extends Model
{
    protected $table = 'camp_referee_jearsy_numbers';

    protected $fillable = [
        'camp_id',
        'referee_id',
        'jersey_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the camp that owns the jersey number
     */
    public function camp(): BelongsTo
    {
        return $this->belongsTo(Camp::class);
    }

    /**
     * Get the referee that owns the jersey number
     */
    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Scope to get jersey number for specific camp and referee
     */
    public function scopeForCampAndReferee($query, $campId, $refereeId)
    {
        return $query->where('camp_id', $campId)
            ->where('referee_id', $refereeId);
    }

    /**
     * Check if jersey number exists in a specific camp
     */
    public static function isJerseyNumberTaken($campId, $jerseyNumber, $excludeRefereeId = null)
    {
        $query = self::where('camp_id', $campId)
            ->where('jersey_number', $jerseyNumber);

        if ($excludeRefereeId) {
            $query->where('referee_id', '!=', $excludeRefereeId);
        }

        return $query->exists();
    }
}
