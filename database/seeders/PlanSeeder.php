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
        // Delete all existing plans to avoid duplicates during re-seeding
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

        // Plan 2: Starter Plan ($10.00 / month - $0.60 rate, 3 Bank Accounts)
        Plan::create([
            'id' => 2,
            'name' => 'Starter Plan',
            'duration' => '1 month',
            'original_price' => 10.00,
            'discounted_price' => 10.00,
            'monthly_price' => 10.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.60',
                    'tooltipText' => 'Print checks online instantly from any bank account on any printer ($0.60 USD per check).'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.60',
                    'tooltipText' => 'Email a One-Time printable, trackable check to recipient ($0.60 USD per email check).'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.60',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail your check in one click on the same business day ($0.60 USD).'
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

        // Plan 3: Professional Plan ($20.00 / month - $0.50 rate, 5 Bank Accounts)
        Plan::create([
            'id' => 3,
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

        // Plan 4: 100 eChecks Package ($64.00 total - $0.64 / check rate)
        Plan::create([
            'id' => 4,
            'name' => '100 eChecks',
            'duration' => 'Lifetime',
            'original_price' => 64.00,
            'discounted_price' => 64.00,
            'monthly_price' => 64.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.64',
                    'tooltipText' => 'Print checks online instantly at $0.64 USD per check.'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.64',
                    'tooltipText' => 'Email a One-Time printable, trackable eCheck at $0.64 USD per check.'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.64',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail check in one click at $0.64 USD.'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 5,
                    'tooltipText' => 'Add up to 5 US bank accounts.'
                ],
                [
                    'label' => 'Helps lower fraud risk',
                    'isCheckmark' => true,
                    'tooltipText' => 'Advanced fraud prevention and security verification.'
                ],
                [
                    'label' => 'Integrates with AP processes',
                    'isCheckmark' => true,
                    'tooltipText' => 'Seamless accounts payable workflow integration.'
                ],
                [
                    'label' => 'Flexible, pay-as-you-go model',
                    'isCheckmark' => true,
                    'tooltipText' => 'Use credits anytime with zero monthly commitment.'
                ],
                [
                    'label' => 'Reduces payment costs',
                    'isCheckmark' => true,
                    'tooltipText' => 'Lower overall transaction and handling expenses.'
                ],
            ],
        ]);

        // Plan 5: 250 eChecks Package ($150.00 total - $0.60 / check rate)
        Plan::create([
            'id' => 5,
            'name' => '250 eChecks',
            'duration' => 'Lifetime',
            'original_price' => 150.00,
            'discounted_price' => 150.00,
            'monthly_price' => 150.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.60',
                    'tooltipText' => 'Print checks online instantly at $0.60 USD per check.'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.60',
                    'tooltipText' => 'Email a One-Time printable, trackable eCheck at $0.60 USD per check.'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.60',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail check in one click at $0.60 USD.'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 10,
                    'tooltipText' => 'Add up to 10 US bank accounts.'
                ],
                [
                    'label' => 'Helps lower fraud risk',
                    'isCheckmark' => true,
                    'tooltipText' => 'Advanced fraud prevention and security verification.'
                ],
                [
                    'label' => 'Integrates with AP processes',
                    'isCheckmark' => true,
                    'tooltipText' => 'Seamless accounts payable workflow integration.'
                ],
                [
                    'label' => 'Flexible, pay-as-you-go model',
                    'isCheckmark' => true,
                    'tooltipText' => 'Use credits anytime with zero monthly commitment.'
                ],
                [
                    'label' => 'Reduces payment costs',
                    'isCheckmark' => true,
                    'tooltipText' => 'Lower overall transaction and handling expenses.'
                ],
            ],
        ]);

        // Plan 6: 500 eChecks Package ($290.00 total - $0.58 / check rate)
        Plan::create([
            'id' => 6,
            'name' => '500 eChecks',
            'duration' => 'Lifetime',
            'original_price' => 290.00,
            'discounted_price' => 290.00,
            'monthly_price' => 290.00,
            'discount_percentage' => 0,
            'features' => [
                [
                    'label' => 'Check Printing',
                    'price' => '$0.58',
                    'tooltipText' => 'Print checks online instantly at $0.58 USD per check.'
                ],
                [
                    'label' => 'Email Check',
                    'price' => '$0.58',
                    'tooltipText' => 'Email a One-Time printable, trackable eCheck at $0.58 USD per check.'
                ],
                [
                    'label' => 'Mail Check In One Click',
                    'subLabel' => '(USPS / Fedex)',
                    'price' => '$0.58',
                    'isGreen' => true,
                    'tooltipText' => 'Print and mail check in one click at $0.58 USD.'
                ],
                [
                    'label' => 'Bank Accounts Allowed',
                    'value' => 25,
                    'tooltipText' => 'Add up to 25 US bank accounts.'
                ],
                [
                    'label' => 'Helps lower fraud risk',
                    'isCheckmark' => true,
                    'tooltipText' => 'Advanced fraud prevention and security verification.'
                ],
                [
                    'label' => 'Integrates with AP processes',
                    'isCheckmark' => true,
                    'tooltipText' => 'Seamless accounts payable workflow integration.'
                ],
                [
                    'label' => 'Flexible, pay-as-you-go model',
                    'isCheckmark' => true,
                    'tooltipText' => 'Use credits anytime with zero monthly commitment.'
                ],
                [
                    'label' => 'Reduces payment costs',
                    'isCheckmark' => true,
                    'tooltipText' => 'Lower overall transaction and handling expenses.'
                ],
            ],
        ]);
    }
}
