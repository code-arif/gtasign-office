<?php

namespace Modules\Director\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTimeRange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
