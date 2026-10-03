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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Clean old shop data from database
        Schema::disableForeignKeyConstraints();
        ProductQuantityTier::truncate();
        ProductColor::truncate();
        DB::table('shop_product_filter_values')->truncate();
        Product::truncate();
        FilterValue::truncate();
        Filter::truncate();
        Category::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Create Exactly 3 Real Deluxe Categories with AWS S3 Images
        $categories = [
            [
                'name' => 'Laser & Inkjet Business Checks',
                'slug' => 'laser',
                'description' => 'Deluxe laser checks 100% compatible with QuickBooks, Sage, Quicken, and major accounting software.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/categories/laser.png',
                'badge_text' => 'QuickBooks & Software Compatible',
                'feature_items' => [
                    '3-To-A-Page Laser Checks',
                    'Laser Top & Middle Voucher Checks',
                    'QuickBooks & Accounting Software Compatible',
                    '24 lb Premium Security Paper'
                ],
                'cta_button_text' => 'Explore Laser & Inkjet Business Checks',
            ],
            [
                'name' => 'Manual Business Checks',
                'slug' => 'manual',
                'description' => 'Deluxe deskbook and manual business checks with side-stubs for easy record-keeping.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/categories/manual.png',
                'badge_text' => 'Deskbook & Manual',
                'feature_items' => [
                    '3-On-A-Page Desk Checks',
                    'Compact Deskbook Checks',
                    'Perforated End-Stubs',
                    '7-Ring Desk Binder Compatibility'
                ],
                'cta_button_text' => 'Explore Manual Business Checks',
            ],
            [
                'name' => 'High Security Checks',
                'slug' => 'high-security',
                'description' => 'Deluxe high security checks with 30+ anti-fraud security features, metallic hologram, and thermo-ink.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/categories/high-security.png',
                'badge_text' => '30+ Fraud Protections',
                'feature_items' => [
                    'High Security Laser Top Checks',
                    'High Security Laser Bottom Checks',
                    'Safety Hologram Foil & Void Pantograph',
                    'Chemical-Wash & Alteration Tamper Protection'
                ],
                'cta_button_text' => 'Explore High Security Checks',
            ],
        ];

        $categoryModels = [];
        foreach ($categories as $cat) {
            $categoryModels[$cat['slug']] = Category::create($cat);
        }

        // 2. Filter Groups & Option Values
        $filtersData = [
            'Security Level' => ['High Security', 'Standard'],
            'Format' => ['3-On-A-Page', 'Deskbook', 'Laser 3-Up', 'Laser Top Voucher', 'Laser Middle Voucher', 'Laser Bottom Voucher', 'Accessory'],
            'Parts / Copies' => ['Single - 1', 'Duplicate - 2', 'Triplicate - 3'],
            'Specialty Purpose' => ['Accounts Payable', 'Payroll', 'General Purpose'],
            'Check Size' => ['Business Size', 'Compact Size'],
        ];

        $filterValueModels = [];
        foreach ($filtersData as $filterName => $values) {
            $filter = Filter::create([
                'name' => $filterName,
                'slug' => Str::slug($filterName),
                'is_active' => true,
            ]);

            foreach ($values as $val) {
                $fv = FilterValue::create([
                    'filter_id' => $filter->id,
                    'value' => $val,
                    'slug' => Str::slug($val),
                ]);
                $filterValueModels[$val] = $fv->id;
            }
        }

        // 3. Real Deluxe Products Data Mapped across Categories with AWS S3 Images
        $productsData = [
            // Product 1: 53220HS (Manual Category)
            [
                'category_slug' => 'manual',
                'item_code' => '53220HS',
                'title' => 'Deluxe High Security 3-On-A-Page Business Size Checks with End Stubs',
                'subtitle' => 'High security manual 3-up checks with side-stubs for record keeping & 30+ anti-fraud protections.',
                'description' => 'Deluxe High Security 3-on-a-page business size checks feature multi-stage security features protecting against check fraud. Each check sheet has 3 business-size checks with perforated side-stubs for easy record keeping, binder punching, and check register compatibility.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_main.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_gallery_1.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_gallery_2.png',
                ],
                'starting_quantity' => 250,
                'starting_price' => 164.99,
                'badge' => 'High Security',
                'template_type' => 'business_deskbook_3up',
                'in_stock' => true,
                'filters' => ['High Security', '3-On-A-Page', 'Deskbook', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_color_classic-blue.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_color_emerald-green.png'],
                    ['color_name' => 'Sun Yellow', 'hex_code' => '#d97706', 'bg_class' => 'bg-amber-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_color_sun-yellow.png'],
                    ['color_name' => 'Burgundy', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_color_burgundy.png'],
                    ['color_name' => 'Tan Leatherette', 'hex_code' => '#b45309', 'bg_class' => 'bg-amber-700', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/53220hs_color_tan-leatherette.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 164.99, 'price_per_check' => 0.66, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 249.99, 'price_per_check' => 0.50, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 389.99, 'price_per_check' => 0.39, 'is_popular' => false],
                    ['quantity' => 2000, 'price' => 599.99, 'price_per_check' => 0.30, 'is_popular' => false],
                ]
            ],
            // Product 2: DLA104 (Laser Category)
            [
                'category_slug' => 'laser',
                'item_code' => 'DLA104',
                'title' => 'Deluxe Laser 3-To-A-Page Checks, Lined',
                'subtitle' => 'Lined 3-up laser business checks compatible with QuickBooks, Sage, Quicken & laser/inkjet printers.',
                'description' => 'Deluxe DLA104 3-to-a-page lined laser business checks designed for accounting software compatibility. Printed 3 checks per sheet with horizontal perforations between checks, featuring pre-printed lines for handwritten or software-printed entries.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_main.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_gallery_1.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_gallery_2.png',
                ],
                'starting_quantity' => 150,
                'starting_price' => 178.99,
                'badge' => 'Best Seller',
                'template_type' => 'laser_3up',
                'in_stock' => true,
                'filters' => ['Standard', 'Laser 3-Up', 'Single - 1', 'General Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_color_classic-blue.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_color_emerald-green.png'],
                    ['color_name' => 'Sun Yellow', 'hex_code' => '#d97706', 'bg_class' => 'bg-amber-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_color_sun-yellow.png'],
                    ['color_name' => 'Charcoal Gray', 'hex_code' => '#475569', 'bg_class' => 'bg-slate-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_color_charcoal-gray.png'],
                    ['color_name' => 'Burgundy', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dla104_color_burgundy.png'],
                ],
                'tiers' => [
                    ['quantity' => 150, 'price' => 178.99, 'price_per_check' => 1.19, 'is_popular' => false],
                    ['quantity' => 300, 'price' => 271.99, 'price_per_check' => 0.91, 'is_popular' => false],
                    ['quantity' => 600, 'price' => 362.99, 'price_per_check' => 0.60, 'is_popular' => true],
                    ['quantity' => 900, 'price' => 449.99, 'price_per_check' => 0.50, 'is_popular' => false],
                    ['quantity' => 1200, 'price' => 534.99, 'price_per_check' => 0.45, 'is_popular' => false],
                ]
            ],
            // Product 3: SSLT103 (High Security Category)
            [
                'category_slug' => 'high-security',
                'item_code' => 'SSLT103',
                'title' => 'Deluxe High Security Laser Top Check, Lined',
                'subtitle' => 'QuickBooks & Quicken compatible top-format voucher check with 30+ anti-fraud security features & hologram.',
                'description' => 'Deluxe High Security SSLT103 top-format laser checks provide maximum protection against check alteration and fraud. Designed specifically for QuickBooks, Quicken, and major accounting applications, featuring 1 check at top with 2 perforated voucher stubs below.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_main.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_gallery_1.png',
                ],
                'starting_quantity' => 250,
                'starting_price' => 247.99,
                'badge' => 'High Security',
                'template_type' => 'laser_top_voucher',
                'in_stock' => true,
                'filters' => ['High Security', 'Laser Top Voucher', 'Single - 1', 'Payroll', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Prismatic Blue', 'hex_code' => '#0284c7', 'bg_class' => 'bg-sky-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_color_prismatic-blue.png'],
                    ['color_name' => 'Prismatic Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_color_prismatic-green.png'],
                    ['color_name' => 'Prismatic Maroon', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslt103_color_prismatic-maroon.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 247.99, 'price_per_check' => 0.99, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 349.99, 'price_per_check' => 0.70, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 529.99, 'price_per_check' => 0.53, 'is_popular' => false],
                ]
            ],
            // Product 4: DLT103 (Laser Category)
            [
                'category_slug' => 'laser',
                'item_code' => 'DLT103',
                'title' => 'Deluxe Laser Top Checks, Lined',
                'subtitle' => 'Top-format computer voucher check sheet compatible with QuickBooks, Quicken, & Xero accounting software.',
                'description' => 'Deluxe DLT103 lined laser top checks feature 1 check at top and 2 remittance voucher stubs below. 100% compatible with laser and inkjet printers for payroll and accounts payable.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_main.png',
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_gallery_1.png'
                ],
                'starting_quantity' => 250,
                'starting_price' => 191.99,
                'badge' => 'QuickBooks Compatible',
                'template_type' => 'laser_top_voucher',
                'in_stock' => true,
                'filters' => ['Standard', 'Laser Top Voucher', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_color_classic-blue.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_color_emerald-green.png'],
                    ['color_name' => 'Tan Marble', 'hex_code' => '#d97706', 'bg_class' => 'bg-amber-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlt103_color_tan-marble.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 191.99, 'price_per_check' => 0.77, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 284.99, 'price_per_check' => 0.57, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 429.99, 'price_per_check' => 0.43, 'is_popular' => false],
                ]
            ],
            // Product 5: DLM108 (Laser Category)
            [
                'category_slug' => 'laser',
                'item_code' => 'DLM108',
                'title' => 'Deluxe Laser Middle Checks, Lined',
                'subtitle' => 'Middle-format computer voucher check compatible with Sage 50, MAS, and Peachtree software.',
                'description' => 'Deluxe DLM108 laser middle checks position the check face in the middle of the sheet with top and bottom voucher stubs. Optimized for Sage 50, MAS 90/200, and Peachtree financial accounting systems.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlm108_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlm108_main.png'
                ],
                'starting_quantity' => 250,
                'starting_price' => 191.99,
                'badge' => 'Sage Compatible',
                'template_type' => 'laser_middle_voucher',
                'in_stock' => true,
                'filters' => ['Standard', 'Laser Middle Voucher', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlm108_color_classic-blue.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dlm108_color_emerald-green.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 191.99, 'price_per_check' => 0.77, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 284.99, 'price_per_check' => 0.57, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 429.99, 'price_per_check' => 0.43, 'is_popular' => false],
                ]
            ],
            // Product 6: SSLB100 (High Security Category)
            [
                'category_slug' => 'high-security',
                'item_code' => 'SSLB100',
                'title' => 'Deluxe High Security Laser Bottom Check',
                'subtitle' => 'Bottom-format high security laser voucher check sheet with hologram foil & thermochromic ink.',
                'description' => 'Deluxe SSLB100 High Security bottom laser checks feature the check at the bottom position with two upper voucher stubs. Built with 30+ anti-counterfeiting features for financial security.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslb100_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslb100_main.png'
                ],
                'starting_quantity' => 250,
                'starting_price' => 247.99,
                'badge' => 'High Security',
                'template_type' => 'laser_bottom_voucher',
                'in_stock' => true,
                'filters' => ['High Security', 'Laser Bottom Voucher', 'Single - 1', 'Accounts Payable', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Prismatic Blue', 'hex_code' => '#0284c7', 'bg_class' => 'bg-sky-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslb100_color_prismatic-blue.png'],
                    ['color_name' => 'Prismatic Maroon', 'hex_code' => '#9f1239', 'bg_class' => 'bg-rose-800', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/sslb100_color_prismatic-maroon.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 247.99, 'price_per_check' => 0.99, 'is_popular' => false],
                    ['quantity' => 500, 'price' => 349.99, 'price_per_check' => 0.70, 'is_popular' => true],
                ]
            ],
            // Product 7: 56600N (Manual Category)
            [
                'category_slug' => 'manual',
                'item_code' => '56600N',
                'title' => 'Deluxe 3-On-A-Page Compact Size Manual Checks',
                'subtitle' => 'Compact deskbook manual business checks with side-tear perforated stubs for record keeping.',
                'description' => 'Deluxe 56600N compact 3-to-a-page manual checkbook designed for portable business check writing with side stubs for quick accounting entries.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/56600n_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/56600n_main.png'
                ],
                'starting_quantity' => 250,
                'starting_price' => 181.99,
                'badge' => 'Compact Size',
                'template_type' => 'compact_deskbook',
                'in_stock' => true,
                'filters' => ['Standard', 'Compact Size', 'Deskbook', 'Single - 1', 'General Purpose'],
                'colors' => [
                    ['color_name' => 'Classic Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/56600n_color_classic-blue.png'],
                    ['color_name' => 'Emerald Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/56600n_color_emerald-green.png'],
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 181.99, 'price_per_check' => 0.73, 'is_popular' => true],
                    ['quantity' => 500, 'price' => 269.99, 'price_per_check' => 0.54, 'is_popular' => false],
                ]
            ],
            // Product 8: 80001B (Laser Category)
            [
                'category_slug' => 'laser',
                'item_code' => '80001B',
                'title' => 'Deluxe Blank MICR Laser Check Stock Paper, Top Check',
                'subtitle' => 'Unprinted security check paper with MICR line positioning for custom check printing software.',
                'description' => 'Deluxe 80001B blank MICR check stock paper for businesses printing their own custom checks with VersaCheck or accounting software. Features top check positioning and security watermark.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/80001b_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/80001b_main.png'
                ],
                'starting_quantity' => 500,
                'starting_price' => 69.99,
                'badge' => 'MICR Paper',
                'template_type' => 'laser_top_voucher',
                'in_stock' => true,
                'filters' => ['High Security', 'Laser Top Voucher', 'Single - 1', 'General Purpose', 'Business Size'],
                'colors' => [
                    ['color_name' => 'Security Blue', 'hex_code' => '#0284c7', 'bg_class' => 'bg-sky-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/80001b_color_security-blue.png'],
                    ['color_name' => 'Security Green', 'hex_code' => '#059669', 'bg_class' => 'bg-emerald-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/80001b_color_security-green.png'],
                ],
                'tiers' => [
                    ['quantity' => 500, 'price' => 69.99, 'price_per_check' => 0.14, 'is_popular' => true],
                    ['quantity' => 1000, 'price' => 119.99, 'price_per_check' => 0.12, 'is_popular' => false],
                ]
            ],
            // Product 9: DEP3 (Manual Category)
            [
                'category_slug' => 'manual',
                'item_code' => 'DEP3',
                'title' => 'Deluxe 3-On-A-Page Business Deposit Slips',
                'subtitle' => 'Official Deluxe 3-up business deposit slips with carbonless duplicate/triplicate options.',
                'description' => 'Deluxe DEP3 3-on-a-page deposit books matched to 3-ring desk check binders for quick bank deposits and accurate transaction ledger recording.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dep3_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dep3_main.png'
                ],
                'starting_quantity' => 250,
                'starting_price' => 49.99,
                'badge' => 'Deposit Books',
                'template_type' => 'business_deskbook_3up',
                'in_stock' => true,
                'filters' => ['Standard', 'Accessory', 'Single - 1', 'General Purpose'],
                'colors' => [
                    ['color_name' => 'Standard White/Blue', 'hex_code' => '#2563eb', 'bg_class' => 'bg-blue-600', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/dep3_color_standard-whiteblue.png']
                ],
                'tiers' => [
                    ['quantity' => 250, 'price' => 49.99, 'price_per_check' => 0.20, 'is_popular' => true],
                    ['quantity' => 500, 'price' => 79.99, 'price_per_check' => 0.16, 'is_popular' => false],
                ]
            ],
            // Product 10: 7R100 (Manual Category)
            [
                'category_slug' => 'manual',
                'item_code' => '7R100',
                'title' => 'Deluxe 7-Ring Executive Check Binder',
                'subtitle' => 'Durable 7-ring vinyl executive desk binder for 3-on-a-page manual business checks.',
                'description' => 'Deluxe 7R100 heavy-duty vinyl 7-ring binder specially designed to hold 3-on-a-page deskbook business checks and deposit slips flat for easy writing.',
                'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/7r100_main.png',
                'images' => [
                    'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/7r100_main.png'
                ],
                'starting_quantity' => 1,
                'starting_price' => 34.99,
                'badge' => 'Executive Binder',
                'template_type' => 'business_deskbook_3up',
                'in_stock' => true,
                'filters' => ['Standard', 'Accessory', 'Single - 1', 'General Purpose'],
                'colors' => [
                    ['color_name' => 'Executive Black', 'hex_code' => '#0f172a', 'bg_class' => 'bg-slate-900', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/7r100_color_executive-black.png'],
                    ['color_name' => 'Executive Navy', 'hex_code' => '#1e3a8a', 'bg_class' => 'bg-blue-900', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/7r100_color_executive-navy.png'],
                    ['color_name' => 'Executive Burgundy', 'hex_code' => '#881337', 'bg_class' => 'bg-rose-900', 'image_url' => 'https://zsi-bucket.s3.us-west-1.amazonaws.com/shop/products/7r100_color_executive-burgundy.png'],
                ],
                'tiers' => [
                    ['quantity' => 1, 'price' => 34.99, 'price_per_check' => 34.99, 'is_popular' => true],
                    ['quantity' => 2, 'price' => 59.99, 'price_per_check' => 29.99, 'is_popular' => false],
                ]
            ]
        ];

        // 4. Seed Products
        foreach ($productsData as $pData) {
            $cat = $categoryModels[$pData['category_slug']] ?? null;
            $prod = Product::create([
                'category_id' => $cat ? $cat->id : null,
                'item_code' => $pData['item_code'],
                'title' => $pData['title'],
                'slug' => Str::slug($pData['title'] . '-' . $pData['item_code']),
                'subtitle' => $pData['subtitle'],
                'description' => $pData['description'],
                'image_url' => $pData['image_url'],
                'images' => $pData['images'],
                'starting_quantity' => $pData['starting_quantity'],
                'starting_price' => $pData['starting_price'],
                'badge' => $pData['badge'],
                'template_type' => $pData['template_type'],
                'in_stock' => $pData['in_stock'],
            ]);

            // Sync Relational Pivot Filter Values
            $pvIds = [];
            foreach ($pData['filters'] as $fName) {
                if (isset($filterValueModels[$fName])) {
                    $pvIds[] = $filterValueModels[$fName];
                }
            }
            $prod->filterValues()->sync($pvIds);

            // Sync Colors
            foreach ($pData['colors'] as $c) {
                $prod->colors()->create($c);
            }

            // Sync Tiers
            foreach ($pData['tiers'] as $t) {
                $prod->quantityTiers()->create($t);
            }
        }
    }
}
