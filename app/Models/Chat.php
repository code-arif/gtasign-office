<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'room_id',
        'text',
        'file',
        'type',
        'order_id',
        'custom_offer_id',
        'delivery_id',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customOffer()
    {
        return $this->belongsTo(CustomOffer::class);
    }

    public function delivery()
    {
        return $this->belongsTo(OrderDeliveries::class, 'delivery_id');
    }

    public function isTextMessage()
    {
        return $this->type === 'text';
    }

    public function isCustomOffer()
    {
        return $this->type === 'custom_offer';
    }

    public function isDelivery()
    {
        return in_array($this->type, ['delivery_submitted', 'delivery_approved', 'delivery_sent']);
    }

    public function isExtensionRequest()
    {
        return $this->type === 'extension_request';
    }
}
