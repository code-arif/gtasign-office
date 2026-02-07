<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getFullUrlAttribute()
    {
        return Storage::disk('public')->url($this->path);
    }

    protected static function booted()
    {
        static::deleting(function ($document) {
            // Delete physical file when model is deleted
            if (Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
        });
    }
}
