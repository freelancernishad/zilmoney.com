<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop\Category;
use App\Models\Shop\Filter;
use App\Models\Shop\FilterValue;
use App\Models\Shop\Product;
use App\Models\Shop\ProductColor;
use App\Models\Shop\ProductQuantityTier;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories (Deluxe Check Catalog Categories)
        $categories = [
            ['name' => 'Laser & Inkjet Business Checks', 'slug' => 'laser', 'description' => 'Laser checks compatible with QuickBooks, Sage, & all accounting software.'],
            ['name' => 'Manual Business Checks', 'slug' => 'manual', 'description' => 'Compact and deskbook manual checks with stub registers.'],
            ['name' => 'High Security Checks', 'slug' => 'high-security', 'description' => '30+ anti-fraud security features, microprint, and hologram foil.'],
            ['name' => 'Blank Check Stock', 'slug' => 'blank-check-paper', 'description' => 'Unprinted MICR blank check paper for custom check printing software.'],
            ['name' => 'Personal & Pocket Checks', 'slug' => 'personal-checks', 'description' => 'Personal wallet size checks and side-tear duplicate checkbooks.'],
            ['name' => 'Check Accessories & Binders', 'slug' => 'accessories', 'description' => '7-Ring binders, deposit slips, endorsement stamps, and journals.'],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Filter Groups & Option Values
        $filtersData = [
            'Security Level' => ['High Security', 'Standard'],
            'Format' => ['1-On-A-Page', '3-On-A-Page', 'Deskbook', 'One-Write', 'Voucher'],
            'Parts / Copies' => ['Single - 1', 'Duplicate - 2', 'Triplicate - 3', 'Quadruplicate - 4'],
            'Color / Design Type' => ['Charitable Designs', 'Premium Designs', 'Classic'],
            'Specialty Purpose' => ['Accounts Payable', 'Multi Purpose', 'Payroll'],
            'Check Size' => ['Business Size', 'Portable Size'],
        ];

        $filterValueModels = [];
        foreach ($filtersData as $filterName => $values) {
            $filter = Filter::updateOrCreate(
                ['slug' => Str::slug($filterName)],
                ['name' => $filterName, 'is_active' => true]
            );

            foreach ($values as $val) {
                $fv = FilterValue::updateOrCreate(
                    ['filter_id' => $filter->id, 'slug' => Str::slug($val)],
                    ['value' => $val]
                );
                $filterValueModels[$val] = $fv->id;
            }
        }

        // 3. Deluxe Products Catalog
        $products = [
            [
                'category_slug' => 'manual',
                'item_code' => 'ITEM#: 56300N',
                'title' => '3-On-A-Page Compact Size Checks with Side-Tear Voucher',
                'subtitle' => 'Compact 3-to-a-page manual business checks with perforated side stub records.',
                'starting_quantity' => 250,
                'starting_price' => 95.99,
                'badge' => 'Best Seller',
                'filters' => ['Standard', 'Classic', '3-On-A-Page', 'Single - 1', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600'],
                    ['color_name' => 'Antique Tan', 'hex_code' => '#d97706', 'bg_class' => 'bg-amber-600'],
                    ['color_name' => 'Slate Charcoal', 'hex_code' => '#475569', 'bg_class' => 'bg-slate-600'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 95.99, 'price_per_check' => 0.38, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 159.99, 'price_per_check' => 0.32, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 249.99, 'price_per_check' => 0.25, 'is_popular' => false],
                ]
            ],
            [
                'category_slug' => 'manual',
                'item_code' => 'ITEM#: 51100N',
                'title' => 'The Entrepreneur, Compact Size Checks and Register',
                'subtitle' => 'Portable deskbook check package with built-in transaction ledger register.',
                'starting_quantity' => 100,
                'starting_price' => 88.99,
                'badge' => 'Popular',
                'filters' => ['Standard', 'Classic', 'Deskbook', 'Single - 1', 'Accounts Payable', 'Portable Size'],
                'colors' => [
                    ['color_name' => 'Navy Blue', 'hex_code' => '#1e3a8a', 'bg_class' => 'bg-blue-900'],
                    ['color_name' => 'Burgundy', 'hex_code' => '#881337', 'bg_class' => 'bg-rose-900'],
                    ['color_name' => 'Forest Green', 'hex_code' => '#14532d', 'bg_class' => 'bg-green-900'],
                ],
                'tiers' => [
                    ['quantity' => 100, 'price' => 88.99, 'price_per_check' => 0.89, 'is_popular' => false],
                    ['quantity' => 250, 'price' => 139.99, 'price_per_check' => 0.56, 'is_popular' => true],
                ]
            ],
            [
                'category_slug' => 'high-security',
                'item_code' => 'ITEM#: 56300HS',
                'title' => 'High Security 3-On-A-Page Business Checks with Hologram',
                'subtitle' => 'Maximum anti-fraud security manual check featuring multi-dimensional hologram foil strip.',
                'starting_quantity' => 250,
                'starting_price' => 145.00,
                'badge' => 'High Security',
                'filters' => ['High Security', 'Premium Designs', '3-On-A-Page', 'Single - 1', 'Payroll', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Security Blue', 'hex_code' => '#1d4ed8', 'bg_class' => 'bg-blue-700'],
                    ['color_name' => 'Security Green', 'hex_code' => '#047857', 'bg_class' => 'bg-emerald-700'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 145.00, 'price_per_check' => 0.58, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 229.00, 'price_per_check' => 0.45, 'is_popular' => true],
                ]
            ],
            [
                'category_slug' => 'high-security',
                'item_code' => 'ITEM#: 81000HS',
                'title' => 'Deluxe High Security Laser Voucher Checks (Top Check)',
                'subtitle' => 'Top check laser format for QuickBooks with 30 anti-copy features, thermochromatic ink & foil hologram.',
                'starting_quantity' => 250,
                'starting_price' => 129.99,
                'badge' => 'Top Pick',
                'filters' => ['High Security', 'Voucher', '1-On-A-Page', 'Single - 1', 'Premium Designs', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Security Blue/Green', 'hex_code' => '#0284c7', 'bg_class' => 'bg-sky-600'],
                    ['color_name' => 'Security Gold/Maroon', 'hex_code' => '#b45309', 'bg_class' => 'bg-amber-700'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 129.99, 'price_per_check' => 0.52, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 199.99, 'price_per_check' => 0.40, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 299.99, 'price_per_check' => 0.30, 'is_popular' => false],
                ]
            ],
            [
                'category_slug' => 'laser',
                'item_code' => 'ITEM#: 83000N',
                'title' => '3-On-A-Page Laser Business Checks for Accounting Software',
                'subtitle' => 'Standard laser check stock formatted 3-up on 8.5x11 sheet for computer check printing.',
                'starting_quantity' => 250,
                'starting_price' => 99.99,
                'badge' => 'Best Seller',
                'filters' => ['Standard', '3-On-A-Page', 'Single - 1', 'Classic', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 99.99, 'price_per_check' => 0.40, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 165.00, 'price_per_check' => 0.33, 'is_popular' => true],
                ]
            ],
            [
                'category_slug' => 'blank-check-paper',
                'item_code' => 'ITEM#: 80000B',
                'title' => 'Blank High Security Laser Check Paper (Voucher Format)',
                'subtitle' => 'Unprinted MICR blank check stock with security watermark and anti-fraud microprint lines.',
                'starting_quantity' => 500,
                'starting_price' => 69.99,
                'badge' => 'New',
                'filters' => ['High Security', '1-On-A-Page', 'Single - 1', 'Classic', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Prismatic Blue/Green', 'hex_code' => '#0d9488', 'bg_class' => 'bg-teal-600'],
                    ['color_name' => 'Burgundy Marble', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800'],
                ],
                'tiers' => [
                    ['quantity' => 500, 'price' => 69.99, 'price_per_check' => 0.14, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 119.99, 'price_per_check' => 0.12, 'is_popular' => false],
                ]
            ],
            [
                'category_slug' => 'manual',
                'item_code' => 'ITEM#: 56300PINK',
                'title' => 'Susan G. Komen® Breast Cancer Awareness Business Checks',
                'subtitle' => 'Charitable edition 3-to-a-page business check series supporting breast cancer research & awareness.',
                'starting_quantity' => 250,
                'starting_price' => 105.99,
                'badge' => 'Charity',
                'filters' => ['Standard', 'Charitable Designs', '3-On-A-Page', 'Single - 1', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Komen Pink', 'hex_code' => '#db2777', 'bg_class' => 'bg-pink-600'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 105.99, 'price_per_check' => 0.42, 'is_popular' => true],
                    ['quantity' => 500, 'price' => 175.99, 'price_per_check' => 0.35, 'is_popular' => false],
                ]
            ],
            [
                'category_slug' => 'accessories',
                'item_code' => 'ITEM#: ACC-7RING',
                'title' => '7-Ring Deluxe Textured Executive Business Check Binder',
                'subtitle' => 'Durable 7-ring binder with brass corners designed for 3-on-a-page deskbook business checks.',
                'starting_quantity' => 1,
                'starting_price' => 29.99,
                'badge' => 'Accessory',
                'filters' => ['Standard', 'Deskbook', 'Single - 1', 'Classic', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Executive Black', 'hex_code' => '#0f172a', 'bg_class' => 'bg-slate-900'],
                    ['color_name' => 'Burgundy Leather', 'hex_code' => '#4c0519', 'bg_class' => 'bg-rose-950'],
                ],
                'tiers' => [
                    ['quantity' => 1, 'price' => 29.99, 'price_per_check' => 29.99, 'is_popular' => true],
                ]
            ],
        ];

        foreach ($products as $pData) {
            $cat = $categoryModels[$pData['category_slug']] ?? null;
            $prod = Product::updateOrCreate(
                ['item_code' => $pData['item_code']],
                [
                    'category_id' => $cat ? $cat->id : null,
                    'title' => $pData['title'],
                    'slug' => Str::slug($pData['title']),
                    'subtitle' => $pData['subtitle'],
                    'starting_quantity' => $pData['starting_quantity'],
                    'starting_price' => $pData['starting_price'],
                    'badge' => $pData['badge'],
                    'in_stock' => true,
                ]
            );

            // Sync Relational Pivot Filter Values
            $pvIds = [];
            foreach ($pData['filters'] as $fName) {
                if (isset($filterValueModels[$fName])) {
                    $pvIds[] = $filterValueModels[$fName];
                }
            }
            $prod->filterValues()->sync($pvIds);

            // Sync Colors
            $prod->colors()->delete();
            foreach ($pData['colors'] as $c) {
                $prod->colors()->create($c);
            }

            // Sync Tiers
            $prod->quantityTiers()->delete();
            foreach ($pData['tiers'] as $t) {
                $prod->quantityTiers()->create($t);
            }
        }
    }
}
