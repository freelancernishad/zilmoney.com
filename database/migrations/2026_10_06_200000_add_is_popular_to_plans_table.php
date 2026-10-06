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
        if (!Schema::hasColumn('plans', 'is_popular')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->boolean('is_popular')->default(false)->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('plans', 'is_popular')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('is_popular');
            });
        }
    }
};
