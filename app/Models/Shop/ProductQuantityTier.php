<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuantityTier extends Model
{
    use HasFactory;

    protected $table = 'shop_product_quantity_tiers';

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'price_per_check',
        'is_popular',
    ];

    protected $casts = [
        'price' => 'float',
        'price_per_check' => 'float',
        'is_popular' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
