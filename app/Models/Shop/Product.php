<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'shop_products';

    protected $fillable = [
        'category_id',
        'item_code',
        'title',
        'slug',
        'subtitle',
        'image_url',
        'images',
        'description',
        'starting_quantity',
        'starting_price',
        'in_stock',
        'badge',
    ];

    protected $casts = [
        'starting_price' => 'float',
        'in_stock' => 'boolean',
        'images' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function filterValues()
    {
        return $this->belongsToMany(FilterValue::class, 'shop_product_filter_values', 'product_id', 'filter_value_id');
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }

    public function quantityTiers()
    {
        return $this->hasMany(ProductQuantityTier::class, 'product_id');
    }
}
