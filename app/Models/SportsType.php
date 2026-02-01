<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SportsType extends Model
{
    protected $fillable = [
        'sports_name',
        'icon',
        'status',
    ];

    protected $hidden = ['created_at','updated_at','icon'];
}
