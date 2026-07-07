<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $foreignKeys = Schema::getForeignKeys('company_payments');
        $hasForeignKey = collect($foreignKeys)->contains(function ($foreignKey) {
            return $foreignKey['name'] === 'company_payments_payee_id_foreign' 
                || in_array('payee_id', $foreignKey['columns']);
        });

        Schema::table('company_payments', function (Blueprint $table) use ($hasForeignKey) {
            // Drop foreign key first if it exists
            if ($hasForeignKey) {
                $table->dropForeign(['payee_id']);
            }
            // Change column to nullable
            $table->unsignedBigInteger('payee_id')->nullable()->change();
            // Re-add foreign key
            $table->foreign('payee_id')->references('id')->on('payees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $foreignKeys = Schema::getForeignKeys('company_payments');
        $hasForeignKey = collect($foreignKeys)->contains(function ($foreignKey) {
            return $foreignKey['name'] === 'company_payments_payee_id_foreign' 
                || in_array('payee_id', $foreignKey['columns']);
        });

        Schema::table('company_payments', function (Blueprint $table) use ($hasForeignKey) {
            if ($hasForeignKey) {
                $table->dropForeign(['payee_id']);
            }
            $table->unsignedBigInteger('payee_id')->nullable(false)->change();
            $table->foreign('payee_id')->references('id')->on('payees')->cascadeOnDelete();
        });
    }
};
