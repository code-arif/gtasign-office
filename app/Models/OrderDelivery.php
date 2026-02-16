<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_number',
        'message',
        'files',
        'status',
        'revision_reason',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'files' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getFileUrlsAttribute()
    {
        if (!$this->files) {
            return [];
        }

        return collect($this->files)->map(function ($file) {
            return Storage::disk('public')->url($file);
        })->toArray();
    }

    protected static function booted()
    {
        static::deleting(function ($delivery) {
            // Delete physical files
            if ($delivery->files && is_array($delivery->files)) {
                foreach ($delivery->files as $file) {
                    if (Storage::disk('public')->exists($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }
            }
        });
    }
}
