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
        Schema::table('payees', function (Blueprint $table) {
            // Drop JSON arrays
            $table->dropColumn(['address', 'bank_account_details']);

            // Add native TS mapping
            $table->integer('payee_id_account_number')->nullable()->after('phone_number');
            $table->string('address_line1')->nullable()->after('company_name');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->nullable()->after('postal_code');
            $table->string('bank_account_holder_name')->nullable()->after('country');
            $table->string('bank_routing_number')->nullable()->after('bank_account_holder_name');
            $table->string('bank_account_number')->nullable()->after('bank_routing_number');
            $table->string('bank_account_type')->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            $table->dropColumn([
                'payee_id_account_number',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country',
                'bank_account_holder_name',
                'bank_routing_number',
                'bank_account_number',
                'bank_account_type'
            ]);

            $table->json('address')->nullable();
            $table->json('bank_account_details')->nullable();
        });
    }
};
