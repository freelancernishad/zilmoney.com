<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_payment_remittances', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)->nullable()->after('unit_cost');
            $table->decimal('net', 15, 2)->nullable()->after('discount');
            $table->date('invoice_date')->nullable()->after('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payment_remittances', function (Blueprint $table) {
            $table->dropColumn(['discount', 'net', 'invoice_date']);
        });
    }
};
