<?php

namespace Modules\Director\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Director\Database\Factories\RefereeAssignmentFactory;

class RefereeAssignment extends Model
{
    protected $guarded = [];

    public function gameSlot(): BelongsTo
    {
        return $this->belongsTo(GameSlot::class);
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Check if assigned as part of crew
     */
    public function isCrewAssignment(): bool
    {
        return $this->crew_id !== null;
    }
}
