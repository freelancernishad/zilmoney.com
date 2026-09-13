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
        // 1. Shop Categories Table
        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Shop Filters Table (Master filter groups like Format, Parts, Security Level)
        Schema::create('shop_filters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Shop Filter Values Table (Individual values under each filter group)
        Schema::create('shop_filter_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filter_id')->constrained('shop_filters')->onDelete('cascade');
            $table->string('value');
            $table->string('slug');
            $table->timestamps();
        });

        // 4. Shop Products Table
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('shop_categories')->onDelete('set null');
            $table->string('item_code')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->integer('starting_quantity')->default(250);
            $table->decimal('starting_price', 10, 2)->default(0.00);
            $table->boolean('in_stock')->default(true);
            $table->string('badge')->nullable();
            $table->timestamps();
        });

        // 5. Product Filter Value Pivot Table (Relational Checked Options)
        Schema::create('shop_product_filter_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
            $table->foreignId('filter_value_id')->constrained('shop_filter_values')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'filter_value_id']);
        });

        // 6. Shop Product Colors Table
        Schema::create('shop_product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
            $table->string('color_name');
            $table->string('hex_code');
            $table->string('bg_class')->nullable();
            $table->timestamps();
        });

        // 7. Shop Product Quantity Tiers Table
        Schema::create('shop_product_quantity_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('shop_products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('price_per_check', 10, 4);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_product_quantity_tiers');
        Schema::dropIfExists('shop_product_colors');
        Schema::dropIfExists('shop_product_filter_values');
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('shop_filter_values');
        Schema::dropIfExists('shop_filters');
        Schema::dropIfExists('shop_categories');
    }
};
