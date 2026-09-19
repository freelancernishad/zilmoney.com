<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    use HasFactory;

    protected $table = 'shop_product_colors';

    protected $fillable = [
        'product_id',
        'color_name',
        'hex_code',
        'bg_class',
        'image_url',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
