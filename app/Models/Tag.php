<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];


    /**
     * Gigs associated with this tag
     */
    public function gigs()
    {
        return $this->belongsToMany(
            Gig::class,
            'gig_tags',
            'tag_id',
            'gig_id'
        );
    }
}
