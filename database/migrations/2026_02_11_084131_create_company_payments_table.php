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
        Schema::create('company_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('account_id')->constrained()->cascadeOnDelete(); // Source
            $table->foreignId('payee_id')->constrained()->cascadeOnDelete(); // Destination
            
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('draft'); // draft, pending, paid, failed, cancelled
            
            $table->date('issue_date')->nullable();
            
            $table->json('remittance_info')->nullable(); // Invoice No, Description, etc.
            $table->json('delivery_proof')->nullable(); // Date, Note, etc.
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_payments');
    }
};
