<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'image',
        'status',
        'order',
        'link'
    ];

    protected $casts = [
        'status' => 'boolean',
        'order' => 'integer'
    ];
}
