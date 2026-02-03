<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $fillable = [
        'user_id',
        'country',
        'institution_name',
        'degree',
        'major',
        'graduation_year',
    ];

    /**
     * Relation: user table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
