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
        Schema::table('plaid_items', function (Blueprint $table) {
            if (!Schema::hasColumn('plaid_items', 'institution_logo')) {
                $table->longText('institution_logo')->nullable()->after('institution_name');
            }
        });

        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'institution_name')) {
                $table->string('institution_name')->nullable()->after('official_name');
            }
            if (!Schema::hasColumn('accounts', 'institution_logo')) {
                $table->longText('institution_logo')->nullable()->after('institution_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plaid_items', function (Blueprint $table) {
            if (Schema::hasColumn('plaid_items', 'institution_logo')) {
                $table->dropColumn('institution_logo');
            }
        });

        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'institution_name')) {
                $table->dropColumn(['institution_name', 'institution_logo']);
            }
        });
    }
};
