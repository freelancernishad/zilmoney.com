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
            $table->json('contacts')->nullable()->after('notes');
            $table->json('todos')->nullable()->after('contacts');
            $table->json('comments')->nullable()->after('todos');
            $table->json('attachments')->nullable()->after('comments');
            $table->json('audit_trials')->nullable()->after('attachments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payees', function (Blueprint $table) {
            $table->dropColumn(['contacts', 'todos', 'comments', 'attachments', 'audit_trials']);
        });
    }
};
