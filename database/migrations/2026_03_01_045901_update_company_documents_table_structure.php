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
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn(['type', 'file_path', 'original_name', 'mime_type', 'status', 'metadata']);

            $table->string('formation_document')->nullable();
            $table->string('ownership_document')->nullable();
            $table->string('principal_officer_id')->nullable();
            $table->json('supporting_documents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_documents', function (Blueprint $table) {
            $table->dropColumn(['formation_document', 'ownership_document', 'principal_officer_id', 'supporting_documents']);

            $table->string('type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('status')->nullable();
            $table->json('metadata')->nullable();
        });
    }
};
