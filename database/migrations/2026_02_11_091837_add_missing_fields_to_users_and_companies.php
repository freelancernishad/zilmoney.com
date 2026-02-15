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
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_verification')->default(false);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('ssn')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_verification',
                'first_name',
                'last_name',
                'display_name',
                'ssn',
                'date_of_birth',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'postal_code',
                'country',
            ]);
        });
        
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
