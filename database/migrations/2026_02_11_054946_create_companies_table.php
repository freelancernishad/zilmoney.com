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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Basic Info
            $table->string('legal_business_name');
            $table->string('dba')->nullable();
            $table->string('entity_type')->nullable(); // LLC, Corp, etc.
            $table->string('country')->nullable();
            
            // Contact
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            
            // Business Details
            $table->string('business_in')->nullable(); // Deployment scope?
            $table->string('industry')->nullable();
            $table->text('description')->nullable();
            $table->date('formation_date')->nullable();
            
            // Verification
            $table->string('verification_photo_id')->nullable(); // Path to file
            $table->string('tax_id')->nullable(); // SSN or EIN
            
            // Addresses (JSON)
            $table->json('physical_address')->nullable();
            $table->json('legal_registered_address')->nullable();
            
            // Ownership / Control
            $table->string('title')->nullable(); // User's title in company
            $table->boolean('control_person')->default(false);
            $table->boolean('beneficial_owner')->default(false);
            $table->string('percentage_ownership')->nullable(); // If beneficial owner

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
