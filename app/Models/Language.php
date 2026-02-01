<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'display_name',
    ];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class, 'profiles_languages')
            ->withPivot(['proficiency_level'])
            ->withTimestamps();
    }
}

