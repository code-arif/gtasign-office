<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GigTag extends Pivot
{
    protected $table = 'gig_tags';

    protected $fillable = [
        'gig_id',
        'tag_id',
    ];

    public $timestamps = true;

    /**
     * Related gig
     */
    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    /**
     * Related tag
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
