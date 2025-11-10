<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'stripe_product_id',
        'stripe_price_id',
        'price',
        'interval',
    ];

    protected $casts = [
       'is_active' => 'boolean'
    ];

    public function features()
    {
        return $this->hasMany(Planfeature::class);
    }

}
