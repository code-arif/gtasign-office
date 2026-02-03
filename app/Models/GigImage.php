<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigImage extends Model
{
    protected $fillable = [
        'gig_id',
        'path',
        'is_primary',
        'sort_order',
    ];

    /**
     * The gig this image belongs to.
     */
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
