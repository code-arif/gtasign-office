<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'awarded_by',
        'year',
        'file_path',
    ];

    /**
     * Relations: User Table
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
