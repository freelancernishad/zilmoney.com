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
        Schema::table('shop_products', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_products', 'template_type')) {
                $table->string('template_type')->default('business_deskbook_3up')->nullable()->after('badge');
            }
            if (!Schema::hasColumn('shop_products', 'preview_config')) {
                $table->json('preview_config')->nullable()->after('template_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn(['template_type', 'preview_config']);
        });
    }
};
