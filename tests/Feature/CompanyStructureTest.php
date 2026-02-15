<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Zilmoney\BusinessDetail;
use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\Payee;
use App\Models\Zilmoney\Payment;
use App\Models\Zilmoney\PersonalInfo;
use Tests\TestCase;

class CompanyStructureTest extends TestCase
{
    // ...

    public function test_company_payment_flow()
    {
        // 1. Create User
        $user = User::factory()->create();
        
        // Create Personal Info
        $user->personalInfo()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $user->email,
        ]);

        // 2. Create BusinessDetail (Company)
        $company = BusinessDetail::create([
            'user_id' => $user->id,
            'legal_business_name' => 'Acme Corp',
            'email' => 'contact@acme.com'
        ]);

        $this->assertDatabaseHas('companies', ['legal_business_name' => 'Acme Corp']);

        // 3. Create Account
        $account = Account::create([
            'company_id' => $company->id,
            'account_holder_name' => 'Acme Main',
            'account_number' => '1234567890',
            'routing_number' => '987654321',
        ]);

        $this->assertDatabaseHas('accounts', ['account_holder_name' => 'Acme Main']);

        // 4. Create Payee
        $payee = Payee::create([
            'company_id' => $company->id,
            'type' => 'vendor',
            'payee_name' => 'Vendor Inc',
            'entity_type' => 'business',
        ]);

        $this->assertDatabaseHas('payees', ['payee_name' => 'Vendor Inc']);

        // 5. Create Payment
        $payment = Payment::create([
            'company_id' => $company->id,
            'account_id' => $account->id,
            'payee_id' => $payee->id,
            'amount' => 100.00,
            'status' => 'pending',
            'issue_date' => now(),
        ]);

        $this->assertDatabaseHas('company_payments', ['amount' => 100.00]);
        $this->assertEquals($company->id, $payment->businessDetail->id);
        $this->assertEquals($account->id, $payment->account->id);
        $this->assertEquals($payee->id, $payment->payee->id);

        // 6. Create Logs
        $payment->logs()->create([
            'status' => 'pending',
            'initiated_by' => $user->id,
            'note' => 'Initial creation',
        ]);

        $this->assertDatabaseHas('company_payment_logs', ['note' => 'Initial creation']);

        // 7. Verify User Structure (JSON)
        $user->refresh();
        $user->load(['personalInfo', 'businessDetails', 'deviceLogs']);
        
        $userJson = $user->toArray();
        
        $this->assertArrayHasKey('personal_info', $userJson);
        $this->assertArrayHasKey('business_details', $userJson);
        $this->assertArrayHasKey('documents', $userJson);
        $this->assertArrayHasKey('accounts', $userJson);
        $this->assertArrayHasKey('payees', $userJson);
        $this->assertArrayHasKey('device_logs', $userJson);
        
        // Verify Business Details has controllers
        $this->assertArrayHasKey('controllers', $userJson['business_details']);
    }
}
