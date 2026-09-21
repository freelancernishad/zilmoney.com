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
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('description');
            $table->string('badge_text')->nullable()->after('image_url');
            $table->json('feature_items')->nullable()->after('badge_text');
            $table->string('cta_button_text')->nullable()->after('feature_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_categories', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'badge_text', 'feature_items', 'cta_button_text']);
        });
    }
};
