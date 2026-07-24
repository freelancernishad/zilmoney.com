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
            $table->boolean('request_bank_account')->default(false)->after('company_name');
            $table->string('bank_name')->nullable()->after('bank_account_type');
            $table->string('swift_code')->nullable()->after('bank_name');
            $table->string('iban')->nullable()->after('swift_code');
            $table->string('intl_bank_country')->nullable()->after('iban');
            $table->string('intl_bank_address')->nullable()->after('intl_bank_country');
            $table->string('tax_id')->nullable()->after('intl_bank_address');
            $table->text('notes')->nullable()->after('tax_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            $table->dropColumn([
                'request_bank_account',
                'bank_name',
                'swift_code',
                'iban',
                'intl_bank_country',
                'intl_bank_address',
                'tax_id',
                'notes',
            ]);
        });
    }
};
