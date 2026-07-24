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
        Schema::table('company_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('company_payments', 'signature_image')) {
                $table->string('signature_image')->nullable()->after('memo');
            }
            if (!Schema::hasColumn('company_payments', 'signature_image_url')) {
                $table->text('signature_image_url')->nullable()->after('signature_image');
            }
            if (!Schema::hasColumn('company_payments', 'company_name')) {
                $table->string('company_name')->nullable()->after('signature_image_url');
            }
            if (!Schema::hasColumn('company_payments', 'company_address')) {
                $table->text('company_address')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('company_payments', 'company_logo_url')) {
                $table->text('company_logo_url')->nullable()->after('company_address');
            }
            if (!Schema::hasColumn('company_payments', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('company_logo_url');
            }
            if (!Schema::hasColumn('company_payments', 'bank_routing_number')) {
                $table->string('bank_routing_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('company_payments', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_routing_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_payments', function (Blueprint $table) {
            $table->dropColumn([
                'signature_image',
                'signature_image_url',
                'company_name',
                'company_address',
                'company_logo_url',
                'bank_name',
                'bank_routing_number',
                'bank_account_number',
            ]);
        });
    }
};
