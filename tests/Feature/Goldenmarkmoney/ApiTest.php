<?php

namespace Tests\Feature\Goldenmarkmoney;

use App\Models\User;
use App\Models\Goldenmarkmoney\BusinessDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoint_returns_data()
    {
        $user = User::factory()->create();
        
        // Create business for user
        BusinessDetail::create([
            'user_id' => $user->id,
            'legal_business_name' => 'Data Test Biz',
            'email' => 'data@test.com'
        ]);
        
        $response = $this->actingAs($user)->getJson('/api/goldenmarkmoney/dashboard');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'overview',
                     'recent_transactions',
                     'business_profile'
                 ]);
    }

    public function test_business_profile_crud()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // CREATE
        $response = $this->postJson('/api/goldenmarkmoney/business', [
            'legal_business_name' => 'New CRUD Biz',
            'email' => 'newcrud@biz.com'
        ]);
        
        $response->assertStatus(201);
        $businessId = $response->json('id');

        // GET
        $response = $this->getJson("/api/goldenmarkmoney/business/{$businessId}");
        $response->assertStatus(200)
                 ->assertJsonFragment(['legal_business_name' => 'New CRUD Biz']);
    }
}
