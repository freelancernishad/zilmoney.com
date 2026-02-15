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
        $settings = [
            ['key' => 'plaid_client_id', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'plaid_secret', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'plaid_environment', 'value' => 'sandbox', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('system_settings')->insertOrIgnore($settings);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            //
        });
    }
};
