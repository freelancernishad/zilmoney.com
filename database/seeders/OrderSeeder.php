<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shop\Order;
use App\Models\Shop\Product;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $prod1 = $products[0] ?? null;
        $prod2 = $products[1] ?? null;
        $prod3 = $products[2] ?? null;

        $orders = [
            [
                'order_number' => 'ORD-98421',
                'customer_name' => 'Acme Financial Inc.',
                'customer_email' => 'billing@acme.com',
                'customer_phone' => '+1 (800) 555-0199',
                'total_amount' => 159.99,
                'payment_status' => 'paid',
                'payment_method' => 'Stripe Credit Card',
                'order_status' => 'processing',
                'tracking_number' => null,
                'custom_check_details' => [
                    'bank_name' => 'JPMorgan Chase Bank, N.A.',
                    'routing_number' => '121000358',
                    'account_number' => '9876543210',
                    'company_name' => 'Acme Financial Inc.',
                    'signature_title' => 'Chief Executive Officer',
                ],
                'items' => [
                    [
                        'product_id' => $prod1 ? $prod1->id : null,
                        'product_title' => $prod1 ? $prod1->title : '3-On-A-Page Compact Size Checks',
                        'item_code' => 'ITEM#: 56300N',
                        'selected_color' => 'Classic Blue',
                        'quantity' => 500,
                        'unit_price' => 0.32,
                        'total_price' => 159.99,
                    ]
                ]
            ],
            [
                'order_number' => 'ORD-98420',
                'customer_name' => 'Global Logistics LLC',
                'customer_email' => 'finance@globallogistics.com',
                'customer_phone' => '+1 (800) 555-0244',
                'total_amount' => 145.00,
                'payment_status' => 'paid',
                'payment_method' => 'ACH Bank Transfer',
                'order_status' => 'printed_shipped',
                'tracking_number' => 'USPS-94001112025539120',
                'custom_check_details' => [
                    'bank_name' => 'Bank of America',
                    'routing_number' => '021000021',
                    'account_number' => '1234567890',
                    'company_name' => 'Global Logistics LLC',
                    'signature_title' => 'Managing Director',
                ],
                'items' => [
                    [
                        'product_id' => $prod3 ? $prod3->id : null,
                        'product_title' => $prod3 ? $prod3->title : 'High Security 3-On-A-Page Business Checks',
                        'item_code' => 'ITEM#: 56300HS',
                        'selected_color' => 'Security Blue',
                        'quantity' => 250,
                        'unit_price' => 0.58,
                        'total_price' => 145.00,
                    ]
                ]
            ],
            [
                'order_number' => 'ORD-98419',
                'customer_name' => 'Summit Accounting',
                'customer_email' => 'accounts@summit.org',
                'customer_phone' => '+1 (800) 555-0377',
                'total_amount' => 249.99,
                'payment_status' => 'paid',
                'payment_method' => 'Check Payment',
                'order_status' => 'delivered',
                'tracking_number' => 'FEDEX-789012345678',
                'custom_check_details' => [
                    'bank_name' => 'Wells Fargo Bank',
                    'routing_number' => '122000247',
                    'account_number' => '5544332211',
                    'company_name' => 'Summit Accounting Group',
                    'signature_title' => 'Chief Financial Officer',
                ],
                'items' => [
                    [
                        'product_id' => $prod2 ? $prod2->id : null,
                        'product_title' => $prod2 ? $prod2->title : 'The Entrepreneur Compact Size Checks',
                        'item_code' => 'ITEM#: 51100N',
                        'selected_color' => 'Navy Blue',
                        'quantity' => 1000,
                        'unit_price' => 0.25,
                        'total_price' => 249.99,
                    ]
                ]
            ],
        ];

        foreach ($orders as $oData) {
            $items = $oData['items'];
            unset($oData['items']);

            $order = Order::updateOrCreate(['order_number' => $oData['order_number']], $oData);
            $order->items()->delete();

            foreach ($items as $item) {
                $order->items()->create($item);
            }
        }
    }
}
