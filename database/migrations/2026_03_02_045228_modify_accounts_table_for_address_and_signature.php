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
        Schema::table('accounts', function (Blueprint $table) {
            // Drop old JSON array column and legacy signature
            $table->dropColumn(['address', 'signature_path']);

            // Add individual address columns matching the TS payload
            $table->string('address_line1')->nullable()->after('phone_number');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->nullable()->after('postal_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Rollback changes
            $table->dropColumn([
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country'
            ]);

            // Restore legacy fields
            $table->json('address')->nullable();
            $table->string('signature_path')->nullable();
        });
    }
};
