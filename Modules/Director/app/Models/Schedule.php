<?php

namespace Modules\Director\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    protected $fillable = [
        'camp_id',
        'game_duration',
        'max_referees_per_slot',
        'status'
    ];

    public function camp()
    {
        return $this->belongsTo(Camp::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ScheduleLocation::class);
    }

    public function timeRanges(): HasMany
    {
        return $this->hasMany(ScheduleTimeRange::class);
    }

    public function gameSlots(): HasMany
    {
        return $this->hasMany(GameSlot::class);
    }
}
