<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planfeature extends Model
{
    //

    protected $fillable = ['plan_id', 'feature'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
