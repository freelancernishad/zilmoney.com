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
        if (Schema::hasTable('account_signatures')) {
            Schema::table('account_signatures', function (Blueprint $table) {
                if (!Schema::hasColumn('account_signatures', 'name')) {
                    $table->string('name')->nullable()->after('account_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_signatures')) {
            Schema::table('account_signatures', function (Blueprint $table) {
                if (Schema::hasColumn('account_signatures', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};
