<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

     protected $fillable = [
        'product_id',
        'code',
        'color_name',
        'price',
        'stock',
        'image_url',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
