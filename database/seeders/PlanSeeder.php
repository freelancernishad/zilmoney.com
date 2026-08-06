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
        // Delete all existing plans
        Plan::query()->delete();

        // Plan 1: Pay As You Go Plan ($0.75 base rate, 1 Bank Account)
        Plan::create([
            'id' => 1,
            'name' => 'Pay As You Go Plan',
            'duration' => '1 month',
            'original_price' => 0.00,
            'discounted_price' => 0.00,
            'monthly_price' => 0.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.75',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer ($0.75 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.75',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient ($0.75 USD per email check).'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.75',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day ($0.75 USD).'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 1,
                    'tooltipText' => 'Add 1 US bank account.'
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

        // Plan 2: Starter Plan ($15.00 / month - 3 Bank Accounts)
        Plan::create([
            'id' => 2,
            'name' => 'Starter Plan',
            'duration' => '1 month',
            'original_price' => 15.00,
            'discounted_price' => 15.00,
            'monthly_price' => 15.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.50',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer ($0.50 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.50',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient ($0.50 USD per email check).'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.50',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day ($0.50 USD).'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 3,
                    'tooltipText' => 'Add up to 3 US bank accounts.'
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

        // Plan 3: Professional Plan ($25.00 / month - 5 Bank Accounts)
        Plan::create([
            'id' => 3,
            'name' => 'Professional Plan',
            'duration' => '1 month',
            'original_price' => 25.00,
            'discounted_price' => 25.00,
            'monthly_price' => 25.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.50',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer ($0.50 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.50',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient ($0.50 USD per email check).'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.50',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day ($0.50 USD).'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 5,
                    'tooltipText' => 'Add up to 5 US bank accounts.'
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
