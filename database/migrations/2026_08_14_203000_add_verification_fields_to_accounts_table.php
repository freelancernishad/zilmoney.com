<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'is_tokenized')) {
                    $table->boolean('is_tokenized')->default(false)->after('mask');
                }
                if (!Schema::hasColumn('accounts', 'verification_status')) {
                    $table->string('verification_status')->default('verified')->after('is_tokenized'); // verified, pending, failed
                }
            });
        }

        $settings = [
            ['key' => 'account_validation_provider', 'value' => 'manual', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'validifi_api_key', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'validifi_client_id', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'validifi_api_url', 'value' => 'https://api.validifi.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'giact_username', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'giact_password', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'giact_api_url', 'value' => 'https://api.giact.com', 'created_at' => now(), 'updated_at' => now()],
        ];

        if (Schema::hasTable('system_settings')) {
            DB::table('system_settings')->insertOrIgnore($settings);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn(['is_tokenized', 'verification_status']);
            });
        }
    }
};
