<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserExperience extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skill_name',
        'level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
