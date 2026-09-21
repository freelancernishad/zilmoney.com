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
        // 1. Categories
        $categories = [
            [
                'name' => 'Laser & Inkjet Business Checks',
                'slug' => 'laser',
                'description' => 'Laser checks compatible with QuickBooks, Sage, & all accounting software.',
                'image_url' => '/images/check-papers-preview.png',
                'badge_text' => 'QuickBooks & Software',
                'feature_items' => ['3-On-A-Page Laser Checks', '1-Up Voucher Payroll Checks', 'QuickBooks & Quicken Formats', 'Multi-Purpose Accounting Checks'],
                'cta_button_text' => 'Explore Laser & Inkjet Business Checks',
            ],
            [
                'name' => 'Manual Business Checks',
                'slug' => 'manual',
                'description' => 'Compact and deskbook manual checks with stub registers.',
                'image_url' => '/images/white-paper-checks-preview.png',
                'badge_text' => 'Portable & Deskbook',
                'feature_items' => ['3-On-A-Page Desk Checks', '1-Up Portable Checkbooks', 'Side-Tear Perforated Stubs', '7-Ring Binder Deskbooks'],
                'cta_button_text' => 'Explore Manual Business Checks',
            ],
            [
                'name' => 'High Security Checks',
                'slug' => 'high-security',
                'description' => '30+ anti-fraud security features, microprint, and hologram foil.',
                'image_url' => '/images/blank-check.jpg',
                'badge_text' => '30+ Security Features',
                'feature_items' => ['High Security Laser Checks', 'High Security Manual Checks', 'Hologram Foil & Void Pantograph', 'Anti-Copy Tamper Protection'],
                'cta_button_text' => 'Explore High Security Checks',
            ],
            [
                'name' => 'Blank Check Stock',
                'slug' => 'blank-check-paper',
                'description' => 'Unprinted MICR blank check paper for custom check printing software.',
                'image_url' => '/images/check-papers-preview.png',
                'badge_text' => 'MICR Paper Stock',
                'feature_items' => ['Top Check Stock', 'Middle Check Stock', 'Bottom Check Stock', 'Blank 3-Up Paper'],
                'cta_button_text' => 'Explore Blank Check Stock',
            ],
            [
                'name' => 'Personal & Pocket Checks',
                'slug' => 'personal-checks',
                'description' => 'Personal wallet size checks and side-tear duplicate checkbooks.',
                'image_url' => '/images/white-paper-checks-preview.png',
                'badge_text' => 'Personal Wallet Size',
                'feature_items' => ['Single Wallet Checks', 'Duplicate Pocket Checks', 'Personal Deskbooks', 'Decorative Designs'],
                'cta_button_text' => 'Explore Personal Checks',
            ],
            [
                'name' => 'Check Accessories & Binders',
                'slug' => 'accessories',
                'description' => '7-Ring binders, deposit slips, endorsement stamps, and journals.',
                'image_url' => '/images/blank-check.jpg',
                'badge_text' => 'Binders & Supplies',
                'feature_items' => ['7-Ring Check Binders', 'Deposit Slips & Books', 'Endorsement Stamps', 'Tax Forms & Envelopes'],
                'cta_button_text' => 'Explore Check Accessories',
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 2. Filter Groups & Option Values
        $filtersData = [
            'Security Level' => ['High Security', 'Standard'],
            'Format' => ['1-On-A-Page', '3-On-A-Page', 'Deskbook', 'One-Write', 'Voucher', 'Compact'],
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

        // 3. Full Deluxe Catalog Data List
        $rawCatalog = [];

        // --- SECTION A: Exactly 93 Deluxe Manual Business Checks ---
        $manualNames = [
            '3-On-A-Page Business Size Checks with Side-Tear Stub',
            'Compact-Size Duplicate Business Checks with Register',
            'The Entrepreneur, Compact Size Deskbook Checks',
            'Compact Newport Deskbook Checks with Stubs',
            'The Traveller, Portable Business Size Checkbook',
            'Desk Size General Purpose Payroll Checks with End-Stub',
            'One-Write System Checks with Master Journal Ledger',
            'Susan G. Komen® Breast Cancer Awareness Business Checks',
            '3-to-a-Page Executive Voucher Business Checks',
            'Triplicate 3-Part Accounts Payable Voucher Checks',
            'Order-Entry Multi-Purpose Manual Business Checks',
            'Side-Tear 2-Part Carbonless Business Checks',
            'Classic Blue Safety 3-On-A-Page Business Checks',
            'Emerald Green Marble Deskbook Business Checks',
            'Antique Tan Leatherette Manual Business Checks',
        ];

        for ($i = 1; $i <= 93; $i++) {
            $baseName = $manualNames[($i - 1) % count($manualNames)];
            $itemCode = "ITEM#: 5100" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "M";
            $title = "Deluxe " . $baseName . " #" . $i;
            $format = ($i % 3 === 0) ? '3-On-A-Page' : (($i % 3 === 1) ? 'Deskbook' : 'Compact');
            $parts = ($i % 2 === 0) ? 'Duplicate - 2' : 'Single - 1';
            $price = 85.99 + (($i % 15) * 6.5);
            $qty = ($i % 2 === 0) ? 250 : 150;

            $rawCatalog[] = [
                'category_slug' => 'manual',
                'item_code' => $itemCode,
                'title' => $title,
                'subtitle' => "Deluxe manual check series #" . $i . " with perforated record stubs & MICR magnetic encoding.",
                'image_url' => ($i % 2 === 0) ? '/images/check-papers-preview.png' : '/images/blank-check.jpg',
                'starting_quantity' => $qty,
                'starting_price' => $price,
                'badge' => ($i % 5 === 0) ? 'Best Seller' : (($i % 4 === 0) ? 'Charity' : 'Popular'),
                'filters' => ['Standard', 'Classic', $format, $parts, 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => '/images/check-papers-preview.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => '/images/white-paper-checks-preview.png'],
                    ['color_name' => 'Antique Tan', 'hex_code' => '#d97706', 'bg_class' => 'bg-amber-600', 'image_url' => '/images/blank-check.jpg'],
                ],
                'tiers' => [
                    ['quantity' => $qty, 'price' => $price, 'price_per_check' => round(($price / $qty), 2), 'is_popular' => false],
                    ['quantity' => $qty * 2, 'price' => round($price * 1.6, 2), 'price_per_check' => round(($price * 1.6 / ($qty * 2)), 2), 'is_popular' => true],
                    ['quantity' => $qty * 4, 'price' => round($price * 2.8, 2), 'price_per_check' => round(($price * 2.8 / ($qty * 4)), 2), 'is_popular' => false],
                ]
            ];
        }

        // --- SECTION B: High Security Checks (20 Products) ---
        for ($i = 1; $i <= 20; $i++) {
            $itemCode = "ITEM#: 810" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "HS";
            $rawCatalog[] = [
                'category_slug' => 'high-security',
                'item_code' => $itemCode,
                'title' => "Deluxe High Security Hologram Voucher Check #" . $i,
                'subtitle' => "Maximum anti-fraud computer check paper with 30+ protective features & thermochromic ink.",
                'image_url' => '/images/white-paper-checks-preview.png',
                'starting_quantity' => 250,
                'starting_price' => 135.99 + ($i * 4),
                'badge' => 'High Security',
                'filters' => ['High Security', 'Premium Designs', 'Voucher', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Prismatic Blue', 'hex_code' => '#0284c7', 'bg_class' => 'bg-sky-600', 'image_url' => '/images/white-paper-checks-preview.png'],
                    ['color_name' => 'Prismatic Maroon', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800', 'image_url' => '/images/check-papers-preview.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 135.99 + ($i * 4), 'price_per_check' => 0.54, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 205.99 + ($i * 6), 'price_per_check' => 0.41, 'is_popular' => true],
                ]
            ];
        }

        // --- SECTION C: Laser & Inkjet Business Checks (15 Products) ---
        for ($i = 1; $i <= 15; $i++) {
            $itemCode = "ITEM#: 830" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "L";
            $rawCatalog[] = [
                'category_slug' => 'laser',
                'item_code' => $itemCode,
                'title' => "Computer Laser Check Sheet #" . $i,
                'subtitle' => "100% compatible with QuickBooks, Sage, Quicken, and Xero accounting software.",
                'image_url' => '/images/check-papers-preview.png',
                'starting_quantity' => 250,
                'starting_price' => 99.99 + ($i * 3),
                'badge' => 'Best Seller',
                'filters' => ['Standard', 'Classic', 'Voucher', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => '/images/check-papers-preview.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => '/images/white-paper-checks-preview.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 99.99 + ($i * 3), 'price_per_check' => 0.40, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 165.99 + ($i * 5), 'price_per_check' => 0.33, 'is_popular' => true],
                ]
            ];
        }

        // --- SECTION D: Blank Check Stock (10 Products) ---
        for ($i = 1; $i <= 10; $i++) {
            $itemCode = "ITEM#: 800" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "B";
            $rawCatalog[] = [
                'category_slug' => 'blank-check-paper',
                'item_code' => $itemCode,
                'title' => "Blank MICR Laser Check Stock Paper #" . $i,
                'subtitle' => "Unprinted security check stock paper with MICR line positioning for custom check printers.",
                'image_url' => '/images/white-paper-checks-preview.png',
                'starting_quantity' => 500,
                'starting_price' => 69.99 + ($i * 2),
                'badge' => 'New',
                'filters' => ['High Security', 'Voucher', 'Single - 1', 'Classic', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Security Blue/Teal', 'hex_code' => '#0d9488', 'bg_class' => 'bg-teal-600', 'image_url' => '/images/white-paper-checks-preview.png']
                ],
                'tiers' => [
                    ['quantity' => 500, 'price' => 69.99 + ($i * 2), 'price_per_check' => 0.14, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 119.99 + ($i * 4), 'price_per_check' => 0.12, 'is_popular' => false],
                ]
            ];
        }

        // --- SECTION E: Personal & Pocket Checks (10 Products) ---
        for ($i = 1; $i <= 10; $i++) {
            $itemCode = "ITEM#: 568" . str_pad((string)$i, 2, '0', STR_PAD_LEFT) . "PW";
            $rawCatalog[] = [
                'category_slug' => 'personal-checks',
                'item_code' => $itemCode,
                'title' => "Personal Wallet Size Duplicate Checkbook #" . $i,
                'subtitle' => "Compact 1-on-a-page personal checkbook with side-tear carbonless duplicate stub.",
                'image_url' => '/images/check-papers-preview.png',
                'starting_quantity' => 40,
                'starting_price' => 54.99 + ($i * 2),
                'badge' => 'Portable',
                'filters' => ['Standard', '1-On-A-Page', 'Duplicate - 2', 'Classic', 'Multi Purpose', 'Portable Size'],
                'colors' => [
                    ['color_name' => 'Slate Black', 'hex_code' => '#1e293b', 'bg_class' => 'bg-slate-800', 'image_url' => '/images/check-papers-preview.png']
                ],
                'tiers' => [
                    ['quantity' => 40, 'price' => 54.99 + ($i * 2), 'price_per_check' => 1.37, 'is_popular' => false],
                    ['quantity' => 80, 'price' => 84.99 + ($i * 3), 'price_per_check' => 1.06, 'is_popular' => true],
                ]
            ];
        }

        // --- SECTION F: Check Accessories & Binders (5 Products) ---
        for ($i = 1; $i <= 5; $i++) {
            $itemCode = "ITEM#: ACC-00" . $i;
            $rawCatalog[] = [
                'category_slug' => 'accessories',
                'item_code' => $itemCode,
                'title' => "Deluxe Executive Business Check Binder & Accessory #" . $i,
                'subtitle' => "Official Deluxe business check accessory and organizing hardware.",
                'image_url' => '/images/blank-check.jpg',
                'starting_quantity' => 1,
                'starting_price' => 24.99 + ($i * 5),
                'badge' => 'Accessory',
                'filters' => ['Standard', 'Deskbook', 'Single - 1', 'Classic', 'Multi Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Executive Black', 'hex_code' => '#0f172a', 'bg_class' => 'bg-slate-900', 'image_url' => '/images/blank-check.jpg']
                ],
                'tiers' => [
                    ['quantity' => 1, 'price' => 24.99 + ($i * 5), 'price_per_check' => 24.99 + ($i * 5), 'is_popular' => true]
                ]
            ];
        }

        // 4. Seed All Deluxe Catalog Items into Database
        foreach ($rawCatalog as $pData) {
            $cat = $categoryModels[$pData['category_slug']] ?? null;
            $prod = Product::updateOrCreate(
                ['item_code' => $pData['item_code']],
                [
                    'category_id' => $cat ? $cat->id : null,
                    'title' => $pData['title'],
                    'slug' => Str::slug($pData['title'] . '-' . Str::after($pData['item_code'], 'ITEM#: ')),
                    'subtitle' => $pData['subtitle'],
                    'image_url' => $pData['image_url'] ?? null,
                    'images' => [
                        $pData['image_url'] ?? '/images/check-papers-preview.png',
                        '/images/white-paper-checks-preview.png',
                        '/images/blank-check.jpg',
                    ],
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
