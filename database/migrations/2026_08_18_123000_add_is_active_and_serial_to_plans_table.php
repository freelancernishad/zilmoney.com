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
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('features');
            }
            if (!Schema::hasColumn('plans', 'serial')) {
                $table->integer('serial')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'serial')) {
                $table->dropColumn('serial');
            }
            if (Schema::hasColumn('plans', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
