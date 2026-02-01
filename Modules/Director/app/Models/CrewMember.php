<?php

namespace Modules\Director\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Director\Database\Factories\CrewMemberFactory;

class CrewMember extends Model
{
    protected $guarded = [];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}
