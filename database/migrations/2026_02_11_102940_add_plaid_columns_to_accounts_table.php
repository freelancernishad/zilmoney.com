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
        if (!Schema::hasColumn('accounts', 'plaid_item_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->foreignId('plaid_item_id')->nullable()->constrained('plaid_items')->onDelete('set null');
                $table->string('plaid_account_id')->nullable();
                $table->string('mask')->nullable();
                $table->string('official_name')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
};
