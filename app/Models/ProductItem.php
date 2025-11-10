<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    protected $fillable = [
        'product_model_id',
        'code',
        'name',
        'image_url',
        'price',
        'stock',
        'is_clearance',
        'discount_price',
        'discount_percentage',

    ];

    protected $table = 'product_items';





    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }
}
