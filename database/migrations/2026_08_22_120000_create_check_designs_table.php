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
        Schema::dropIfExists('check_designs');

        Schema::create('check_designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('name');
            $table->longText('custom_bg_url')->nullable();
            $table->json('positions');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        try {
            Schema::table('check_designs', function (Blueprint $table) {
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // Foreign key constraints are skipped if referenced table uses MyISAM engine
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_designs');
    }
};
