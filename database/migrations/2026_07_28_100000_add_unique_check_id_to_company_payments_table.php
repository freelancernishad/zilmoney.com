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
            if (!Schema::hasColumn('company_payments', 'unique_check_id')) {
                $table->string('unique_check_id')->nullable()->unique()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            if (Schema::hasColumn('company_payments', 'unique_check_id')) {
                $table->dropColumn('unique_check_id');
            }
        });
    }
};
