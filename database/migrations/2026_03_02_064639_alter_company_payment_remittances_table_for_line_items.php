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
        Schema::table('company_payment_remittances', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'original_name']);

            $table->string('invoice_number')->nullable()->after('user_id');
            $table->string('item')->nullable()->after('invoice_number');
            $table->string('description')->nullable()->after('item');
            $table->integer('quantity')->nullable()->after('description');
            $table->decimal('unit_cost', 15, 2)->nullable()->after('quantity');
            $table->decimal('total', 15, 2)->nullable()->after('unit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payment_remittances', function (Blueprint $table) {
            $table->string('file_path');
            $table->string('original_name')->nullable();

            $table->dropColumn([
                'invoice_number',
                'item',
                'description',
                'quantity',
                'unit_cost',
                'total'
            ]);
        });
    }
};
