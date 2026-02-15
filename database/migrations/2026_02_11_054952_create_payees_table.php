<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('type'); // customer, vendor, employee
            $table->string('payee_name');
            $table->string('nick_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            
            $table->string('entity_type'); // individual, business
            $table->string('company_name')->nullable(); // if entity_type business
            
            $table->json('address')->nullable();
            $table->json('bank_account_details')->nullable(); // account_number, routing_number, type
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payees');
    }
};
