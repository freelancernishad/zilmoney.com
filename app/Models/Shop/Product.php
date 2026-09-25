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
        'template_type',
        'preview_config',
    ];

    protected $casts = [
        'starting_price' => 'float',
        'in_stock' => 'boolean',
        'images' => 'array',
        'preview_config' => 'array',
    ];

    public static function getDefaultPreviewPresets(): array
    {
        return [
            'business_deskbook_3up' => [
                'templateName' => '3-On-A-Page Compact Deskbook Check',
                'viewBox' => '0 0 900 300',
                'aspectRatio' => '750 / 640',
                'coords' => [
                    'routing'     => ['x' => 145, 'y' => 260, 'w' => 215, 'h' => 34, 'lineX' => 250, 'label' => 'Routing Number'],
                    'account'     => ['x' => 370, 'y' => 260, 'w' => 300, 'h' => 34, 'lineX' => 520, 'label' => 'Account Number'],
                    'startNumber' => ['x' => 740, 'y' => 14,  'w' => 126, 'h' => 28, 'lineX' => 800, 'label' => 'Check Number'],
                    'bankName'    => ['x' => 390, 'y' => 190, 'w' => 330, 'h' => 56, 'lineX' => 555, 'label' => 'Bank Information'],
                    'company'     => ['x' => 82,  'y' => 14,  'w' => 370, 'h' => 75, 'lineX' => 220, 'label' => 'Company Information'],
                    'logo'        => ['x' => 36,  'y' => 14,  'w' => 50,  'h' => 42, 'lineX' => 68,  'label' => 'Logo Placement'],
                ],
            ],
            'laser_top_check' => [
                'templateName' => 'Laser Top Check (Voucher Bottom)',
                'viewBox' => '0 0 900 300',
                'aspectRatio' => '700 / 640',
                'coords' => [
                    'routing'     => ['x' => 145, 'y' => 255, 'w' => 215, 'h' => 36, 'lineX' => 250, 'label' => 'Routing Number'],
                    'account'     => ['x' => 370, 'y' => 255, 'w' => 300, 'h' => 36, 'lineX' => 520, 'label' => 'Account Number'],
                    'startNumber' => ['x' => 750, 'y' => 18,  'w' => 120, 'h' => 28, 'lineX' => 810, 'label' => 'Check Number'],
                    'bankName'    => ['x' => 400, 'y' => 185, 'w' => 320, 'h' => 52, 'lineX' => 560, 'label' => 'Bank Information'],
                    'company'     => ['x' => 75,  'y' => 14,  'w' => 360, 'h' => 70, 'lineX' => 200, 'label' => 'Company Information'],
                    'logo'        => ['x' => 35,  'y' => 14,  'w' => 52,  'h' => 44, 'lineX' => 65,  'label' => 'Logo Placement'],
                ],
            ],
            'laser_middle_check' => [
                'templateName' => 'Laser Middle Check (Voucher Top & Bottom)',
                'viewBox' => '0 0 900 300',
                'aspectRatio' => '700 / 640',
                'coords' => [
                    'routing'     => ['x' => 145, 'y' => 260, 'w' => 215, 'h' => 34, 'lineX' => 250, 'label' => 'Routing Number'],
                    'account'     => ['x' => 370, 'y' => 260, 'w' => 300, 'h' => 34, 'lineX' => 520, 'label' => 'Account Number'],
                    'startNumber' => ['x' => 740, 'y' => 14,  'w' => 126, 'h' => 28, 'lineX' => 800, 'label' => 'Check Number'],
                    'bankName'    => ['x' => 390, 'y' => 190, 'w' => 330, 'h' => 56, 'lineX' => 555, 'label' => 'Bank Information'],
                    'company'     => ['x' => 82,  'y' => 14,  'w' => 370, 'h' => 75, 'lineX' => 220, 'label' => 'Company Information'],
                    'logo'        => ['x' => 36,  'y' => 14,  'w' => 50,  'h' => 42, 'lineX' => 68,  'label' => 'Logo Placement'],
                ],
            ],
            'laser_3up' => [
                'templateName' => 'Laser 3-On-A-Page (Standard Sheet)',
                'viewBox' => '0 0 900 300',
                'aspectRatio' => '700 / 640',
                'coords' => [
                    'routing'     => ['x' => 145, 'y' => 260, 'w' => 215, 'h' => 34, 'lineX' => 250, 'label' => 'Routing Number'],
                    'account'     => ['x' => 370, 'y' => 260, 'w' => 300, 'h' => 34, 'lineX' => 520, 'label' => 'Account Number'],
                    'startNumber' => ['x' => 740, 'y' => 14,  'w' => 126, 'h' => 28, 'lineX' => 800, 'label' => 'Check Number'],
                    'bankName'    => ['x' => 390, 'y' => 190, 'w' => 330, 'h' => 56, 'lineX' => 555, 'label' => 'Bank Information'],
                    'company'     => ['x' => 82,  'y' => 14,  'w' => 370, 'h' => 75, 'lineX' => 220, 'label' => 'Company Information'],
                    'logo'        => ['x' => 36,  'y' => 14,  'w' => 50,  'h' => 42, 'lineX' => 68,  'label' => 'Logo Placement'],
                ],
            ],
            'deposit_slip' => [
                'templateName' => 'Deposit Ticket / Slip Format',
                'viewBox' => '0 0 900 300',
                'aspectRatio' => '750 / 340',
                'coords' => [
                    'routing'     => ['x' => 145, 'y' => 258, 'w' => 200, 'h' => 34, 'lineX' => 245, 'label' => 'Routing Number'],
                    'account'     => ['x' => 360, 'y' => 258, 'w' => 280, 'h' => 34, 'lineX' => 500, 'label' => 'Account Number'],
                    'startNumber' => ['x' => 750, 'y' => 14,  'w' => 110, 'h' => 28, 'lineX' => 805, 'label' => 'Slip Serial Number'],
                    'bankName'    => ['x' => 380, 'y' => 185, 'w' => 340, 'h' => 54, 'lineX' => 550, 'label' => 'Financial Institution'],
                    'company'     => ['x' => 75,  'y' => 14,  'w' => 360, 'h' => 75, 'lineX' => 210, 'label' => 'Account Holder Information'],
                    'logo'        => ['x' => 30,  'y' => 14,  'w' => 50,  'h' => 42, 'lineX' => 60,  'label' => 'Logo Placement'],
                ],
            ],
        ];
    }

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
