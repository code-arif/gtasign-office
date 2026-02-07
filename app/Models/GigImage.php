<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GigImage extends Model
{
    protected $fillable = [
        'gig_id',
        'path',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * The gig this image belongs to.
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
        static::deleting(function ($image) {
            // Delete physical file when model is deleted
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}
