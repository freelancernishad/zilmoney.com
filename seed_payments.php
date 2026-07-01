<?php

use App\Models\User;
use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\Payee;
use App\Models\Zilmoney\Payment;
use Illuminate\Support\Facades\DB;

// 1. Get the target user (first user or default testing user)
$user = User::where('email', 'freelancernishad123@gmail.com')->first() ?: User::first();
if (!$user) {
    echo "Error: No user found in the database. Please register/create a user first.\n";
    exit(1);
}

// Log in the user in the session context so that auth()->id() works in model boots
auth()->login($user);
echo "Logged in as user: {$user->name} ({$user->email})\n";

// 2. Get business profile
$business = $user->businessDetails()->first();
if (!$business) {
    // Create a mock business
    $business = $user->businessDetails()->create([
        'company_name' => 'Zilmoney Inc',
        'address_line1' => '123 Business Way',
        'city' => 'Silicon Valley',
        'state' => 'CA',
        'postal_code' => '94043',
        'country' => 'US',
    ]);
    echo "Created mock business details for the user.\n";
}

// 3. Get or create accounts (source bank)
$account = Account::where('company_id', $business->id)->first();
if (!$account) {
    $account = Account::create([
        'company_id' => $business->id,
        'bank_name' => 'Chase Bank',
        'account_holder_name' => 'Zilmoney Inc',
        'account_nick_name' => 'Chase Business Checking',
        'routing_number' => '121000248',
        'account_number' => '9876543210',
        'bank_type' => 'checking',
        'balance' => 25000.00,
    ]);
    echo "Created Chase Business Checking account.\n";
} else {
    // Ensure sufficient balance
    if ($account->balance < 10000) {
        $account->update(['balance' => 25000.00]);
        echo "Refreshed account balance to $25,000.00.\n";
    }
}

// 4. Get or create payees (destination)
$payees = Payee::where('company_id', $business->id)->get();
if ($payees->isEmpty()) {
    $payeeList = [
        ['payee_name' => 'Acme Supplies Co.', 'nick_name' => 'Acme Supplies', 'email' => 'billing@acme.com', 'phone_number' => '555-0199', 'address_line1' => '456 Industrial Pkwy', 'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601'],
        ['payee_name' => 'Jane Miller Consulting', 'nick_name' => 'Jane Miller', 'email' => 'jane@miller.com', 'phone_number' => '555-0245', 'address_line1' => '789 Oak Ave Suite 12', 'city' => 'Boston', 'state' => 'MA', 'postal_code' => '02108'],
        ['payee_name' => 'Pacific Power & Light', 'nick_name' => 'Utility Company', 'email' => 'support@pacificpower.com', 'phone_number' => '555-0311', 'address_line1' => '90 Power St', 'city' => 'Portland', 'state' => 'OR', 'postal_code' => '97201'],
    ];

    foreach ($payeeList as $pData) {
        $payees->push(Payee::create(array_merge($pData, ['company_id' => $business->id])));
    }
    echo "Created sample payees.\n";
}

// 5. Generate 15 sample payments
$statuses = ['pending', 'Paid', 'Void'];
$payAsTypes = ['Check', 'ACH', 'Wire'];
$memos = [
    'Office rent payment', 
    'Consulting services invoice #1042', 
    'Monthly electricity bill', 
    'Office supply restocking', 
    'Software licensing renewal', 
    'Marketing campaign phase 1',
];

$checkNumStart = 1001;

DB::beginTransaction();
try {
    // Delete existing sample payments to start clean (optional)
    Payment::where('company_id', $business->id)->delete();
    echo "Purged existing payments for clean seed.\n";

    for ($i = 0; $i < 15; $i++) {
        $payee = $payees->random();
        $status = $statuses[array_rand($statuses)];
        $payAs = $payAsTypes[array_rand($payAsTypes)];
        $amount = rand(5000, 150000) / 100; // $50.00 to $1,500.00
        $memo = $memos[array_rand($memos)];
        $checkNumber = ($payAs === 'Check') ? ($checkNumStart + $i) : null;
        $issueDate = now()->subDays(rand(1, 30));

        $payment = Payment::create([
            'company_id' => $business->id,
            'account_id' => $account->id,
            'payee_id' => $payee->id,
            'pay_from' => 'Bank Account',
            'pay_as' => $payAs,
            'amount' => $amount,
            'status' => $status,
            'issue_date' => $issueDate,
            'check_number' => $checkNumber,
            'memo' => $memo,
        ]);

        // If the payment is Paid/Void, decrement the bank balance accordingly (simulating real operation)
        if ($status === 'Paid') {
            $account->decrement('balance', $amount);
        }

        // Add mock comments to some payments
        if (rand(0, 1)) {
            $payment->comments()->create([
                'user_id' => $user->id,
                'comment' => "Approved by finance manager on " . now()->subDays(1)->toDateString(),
            ]);
        }
    }

    DB::commit();
    echo "Successfully seeded 15 test payments!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error seeding payments: " . $e->getMessage() . "\n";
}
