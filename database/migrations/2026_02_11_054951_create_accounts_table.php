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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->string('account_holder_name');
            $table->string('account_nick_name')->nullable();
            
            // Banking Details
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();
            
            $table->string('type')->nullable(); // Checking, Savings, etc.
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            
            // Complex Data
            $table->json('address')->nullable();
            $table->json('ach_auth_form')->nullable(); // signature, authorizer, etc.
            
            $table->bigInteger('next_check_starting_number')->nullable();
            $table->string('signature_path')->nullable(); // Path to signature file image
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
