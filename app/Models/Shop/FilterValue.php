<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterValue extends Model
{
    use HasFactory;

    protected $table = 'shop_filter_values';

    protected $fillable = [
        'filter_id',
        'value',
        'slug',
    ];

    public function filter()
    {
        return $this->belongsTo(Filter::class, 'filter_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'shop_product_filter_values', 'filter_value_id', 'product_id');
    }
}
