<?php

namespace Modules\Director\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Director\Database\Factories\ScheduleLocationFactory;

class ScheduleLocation extends Model
{
    protected $guarded = [];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function gameSlots(): HasMany
    {
        return $this->hasMany(GameSlot::class);
    }
}
