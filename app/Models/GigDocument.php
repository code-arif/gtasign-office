<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigDocument extends Model
{
    protected $fillable = [
        'gig_id',
        'path',
    ];

    /**
     * The gig this document belongs to.
     */
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }
}
