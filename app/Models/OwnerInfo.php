<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerInfo extends Model
{
    use HasFactory;

    protected $table = 'owner_infos';

    protected $fillable = [
        'name',
        'designation',
        'experience',
        'bio',
        'image',
        'stats',
        'status',
    ];

    protected $casts = [
        'stats' => 'array',
    ];
}
