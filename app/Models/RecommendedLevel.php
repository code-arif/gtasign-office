<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendedLevel extends Model
{
    protected $fillable = ['evaluation_id', 'level'];

    // reverse relation with evaluation table
    public function evaluation()
    {
        return $this->belongsTo(RefereeEvaluation::class, 'evaluation_id');
    }
}
