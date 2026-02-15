<?php
use App\Models\User;
use App\Models\Zilmoney\BusinessDetail;
use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\Payee;
use App\Models\Zilmoney\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_deducts_balance_and_increments_check_number()
    {
        $user = User::factory()->create();
        $business = BusinessDetail::create(['user_id' => $user->id, 'legal_business_name' => 'Test Biz', 'email' => 't@t.com']);
        $account = $business->accounts()->create([
            'account_holder_name' => 'Test Acc',
            'account_number' => '1234567890',  // Use a sensible string for account number
            'routing_number' => '987654321', 
            'type' => 'checking',
            'balance' => 1000.00
        ]);
        $payee = $business->payees()->create(['payee_name' => 'Vendor', 'type' => 'vendor', 'entity_type' => 'business']);

        // 1. Make Payment (Auto check number)
        $response = $this->actingAs($user)->postJson('/api/zilmoney/payments', [
            'account_id' => $account->id,
            'payee_id' => $payee->id,
            'amount' => 100.00,
            'issue_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        // dump($response->json());
        $paymentId = $response->json('id'); 
        // If id is null, maybe it is wrapped? Let's check.
        if (!$paymentId) $paymentId = $response->json('data.id');
        
        // Assertions
        $this->assertDatabaseHas('company_payments', ['id' => $paymentId, 'check_number' => '1001']);
        
        // Refresh account to check balance
        $account->refresh();
        $this->assertEquals(900.00, $account->balance);

        // 2. Make Second Payment (Auto check increment)
        $response2 = $this->actingAs($user)->postJson('/api/zilmoney/payments', [
            'account_id' => $account->id,
            'payee_id' => $payee->id,
            'amount' => 50.00,
            'issue_date' => now()->toDateString(),
        ]);
        
        $response2->assertStatus(201);
        $this->assertDatabaseHas('company_payments', ['check_number' => '1002']);
        
        $account->refresh();
        $this->assertEquals(850.00, $account->balance);
    }

    public function test_payment_fails_insufficient_funds()
    {
        $user = User::factory()->create();
        $business = BusinessDetail::create(['user_id' => $user->id, 'legal_business_name' => 'Test Biz', 'email' => 't@t.com']);
        $account = $business->accounts()->create([
             'account_holder_name' => 'Test Acc',
             'account_number' => '1234567890',
             'routing_number' => '987654321',
             'type' => 'checking',
             'balance' => 10.00 
        ]);
        $payee = $business->payees()->create(['payee_name' => 'Vendor', 'type' => 'vendor', 'entity_type' => 'business']);

        $response = $this->actingAs($user)->postJson('/api/zilmoney/payments', [
            'account_id' => $account->id,
            'payee_id' => $payee->id,
            'amount' => 100.00, // Exceeds balance
            'issue_date' => now()->toDateString(),
        ]);
        
        // dump($response->json());
        $response->assertStatus(400)
                 ->assertJsonFragment(['errMsg' => 'Insufficient funds. Available: 10']); // Based on actual output
    }
}
