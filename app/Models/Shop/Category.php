<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'shop_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
        'badge_text',
        'feature_items',
        'cta_button_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'feature_items' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
