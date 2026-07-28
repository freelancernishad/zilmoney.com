<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('company_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('company_payments', 'email_token')) {
                if (Schema::hasColumn('company_payments', 'unique_check_id')) {
                    $table->string('email_token', 64)->nullable()->unique()->after('unique_check_id');
                } else {
                    $table->string('email_token', 64)->nullable()->unique();
                }
            }
        });
    }

    public function down()
    {
        Schema::table('company_payments', function (Blueprint $table) {
            if (Schema::hasColumn('company_payments', 'email_token')) {
                $table->dropColumn('email_token');
            }
        });
    }
};
