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
        Schema::table('company_payments', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('amount');
            $table->string('payee_id_account_number')->nullable()->after('payee_id');
            $table->unsignedBigInteger('category_id')->nullable()->after('invoice_number');

            $table->foreign('category_id')->references('id')->on('company_payment_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['invoice_number', 'payee_id_account_number', 'category_id']);
        });
    }
};
