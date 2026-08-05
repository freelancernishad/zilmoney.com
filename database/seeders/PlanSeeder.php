<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate plans table if exists
        Plan::query()->delete();

        Plan::create([
            'id' => 1,
            'name' => 'Starter Plan',
            'duration' => '1 month',
            'original_price' => 10.00,
            'discounted_price' => 10.00,
            'monthly_price' => 10.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.50',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer using blank check paper or plain white paper ($0.50 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$1.00',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient.'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$1.25',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day.'
                ],
                [
                    'label' => 'Documents Mailing',
                    'price' => '$1.00',
                    'isGreen' => true,
                    'tooltipText' => 'Mail any kind of document in one click via USPS and FedEx.'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 1,
                    'tooltipText' => 'Add 1 US bank account.'
                ],
                [
                    'label' => 'Users Access',
                    'value' => 1,
                    'tooltipText' => '1 User access.'
                ],
                [
                    'label' => 'Payees & Vendor Management',
                    'isCheckmark' => true,
                    'tooltipText' => 'Store vendor contacts, banking details, and transaction history.'
                ],
                [
                    'label' => 'Digital Signatures & Custom Templates',
                    'isCheckmark' => true,
                    'tooltipText' => 'Upload digital signatures and customize check details.'
                ],
                [
                    'label' => 'Billing & Subscription Management',
                    'isCheckmark' => true,
                    'tooltipText' => 'Manage active subscription, plan changes, and invoices.'
                ],
            ],
        ]);

        Plan::create([
            'id' => 2,
            'name' => 'Professional Plan',
            'duration' => '1 month',
            'original_price' => 20.00,
            'discounted_price' => 20.00,
            'monthly_price' => 20.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.50',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer using blank check paper or plain white paper ($0.50 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$1.00',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient.'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$1.25',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day.'
                ],
                [
                    'label' => 'Documents Mailing',
                    'price' => '$1.00',
                    'isGreen' => true,
                    'tooltipText' => 'Mail any kind of document in one click via USPS and FedEx.'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 3,
                    'tooltipText' => 'Add up to 3 US bank accounts.'
                ],
                [
                    'label' => 'Users Access',
                    'value' => 3,
                    'tooltipText' => '3 Users allowed with multi-level role permission settings.'
                ],
                [
                    'label' => 'Payees & Vendor Management',
                    'isCheckmark' => true,
                    'tooltipText' => 'Store vendor contacts, banking details, and transaction history.'
                ],
                [
                    'label' => 'Digital Signatures & Custom Templates',
                    'isCheckmark' => true,
                    'tooltipText' => 'Upload digital signatures and customize check details.'
                ],
                [
                    'label' => 'Billing & Subscription Management',
                    'isCheckmark' => true,
                    'tooltipText' => 'Manage active subscription, plan changes, and invoices.'
                ],
            ],
        ]);
    }
}
