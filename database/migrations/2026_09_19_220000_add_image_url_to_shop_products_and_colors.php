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
            if (!Schema::hasColumn('shop_products', 'image_url')) {
                $table->string('image_url', 1000)->nullable()->after('subtitle');
            }
            if (!Schema::hasColumn('shop_products', 'images')) {
                $table->json('images')->nullable()->after('image_url');
            }
        });

        Schema::table('shop_product_colors', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_product_colors', 'image_url')) {
                $table->string('image_url', 1000)->nullable()->after('bg_class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            if (Schema::hasColumn('shop_products', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('shop_product_colors', function (Blueprint $table) {
            if (Schema::hasColumn('shop_product_colors', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
