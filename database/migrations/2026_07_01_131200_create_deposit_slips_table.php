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
        Schema::create('deposit_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            
            $table->string('deposit_from')->nullable();
            $table->date('date');
            $table->string('ref_id')->nullable();
            $table->text('memo')->nullable();
            $table->boolean('blank_deposit_slip')->default(false);
            
            $table->json('cash_items')->nullable(); // JSON array of [{amount, cashier_clerk, note}]
            $table->json('check_items')->nullable(); // JSON array of [{amount, from, check_number, cashier_clerk, note}]
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_slips');
    }
};
