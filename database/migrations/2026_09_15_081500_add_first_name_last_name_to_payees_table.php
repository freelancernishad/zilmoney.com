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
        Schema::table('payees', function (Blueprint $table) {
            if (!Schema::hasColumn('payees', 'first_name')) {
                $table->string('first_name')->nullable()->after('type');
            }
            if (!Schema::hasColumn('payees', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            if (Schema::hasColumn('payees', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('payees', 'last_name')) {
                $table->dropColumn('last_name');
            }
        });
    }
};
