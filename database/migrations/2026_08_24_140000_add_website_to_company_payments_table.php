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
            if (!Schema::hasColumn('company_payments', 'website')) {
                $table->string('website')->nullable()->after('company_logo_url');
            }
            if (!Schema::hasColumn('company_payments', 'business_website')) {
                $table->string('business_website')->nullable()->after('website');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            if (Schema::hasColumn('company_payments', 'business_website')) {
                $table->dropColumn('business_website');
            }
            if (Schema::hasColumn('company_payments', 'website')) {
                $table->dropColumn('website');
            }
        });
    }
};
