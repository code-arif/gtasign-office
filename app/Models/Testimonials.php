<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    protected $fillable = [
        'author_name',
        'designation',
        'review_text',
        'author_avatar',
    ];
}
