<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['payee_id']);
            // Change column to nullable
            $table->unsignedBigInteger('payee_id')->nullable()->change();
            // Re-add foreign key
            $table->foreign('payee_id')->references('id')->on('payees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            $table->dropForeign(['payee_id']);
            $table->unsignedBigInteger('payee_id')->nullable(false)->change();
            $table->foreign('payee_id')->references('id')->on('payees')->cascadeOnDelete();
        });
    }
};
