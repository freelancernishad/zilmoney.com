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
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'bank_address_line1')) {
                    $table->string('bank_address_line1')->nullable()->after('institution_logo');
                }
                if (!Schema::hasColumn('accounts', 'bank_city')) {
                    $table->string('bank_city')->nullable()->after('bank_address_line1');
                }
                if (!Schema::hasColumn('accounts', 'bank_state')) {
                    $table->string('bank_state')->nullable()->after('bank_city');
                }
                if (!Schema::hasColumn('accounts', 'bank_postal_code')) {
                    $table->string('bank_postal_code')->nullable()->after('bank_state');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn(['bank_address_line1', 'bank_city', 'bank_state', 'bank_postal_code']);
            });
        }
    }
};
