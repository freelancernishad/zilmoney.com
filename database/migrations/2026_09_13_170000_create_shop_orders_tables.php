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
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');

        // 1. Shop Orders Table
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('paid'); // paid, pending, failed, refunded
            $table->string('payment_method')->default('Stripe Credit Card');
            $table->string('order_status')->default('processing'); // processing, printed_shipped, delivered, cancelled
            $table->string('tracking_number')->nullable();
            $table->json('custom_check_details')->nullable(); // MICR Routing, Account #, Bank Name, Logo
            $table->timestamps();
        });

        // 2. Shop Order Items Table
        Schema::create('shop_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('shop_orders')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_title');
            $table->string('item_code')->nullable();
            $table->string('selected_color')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_order_items');
        Schema::dropIfExists('shop_orders');
    }
};
