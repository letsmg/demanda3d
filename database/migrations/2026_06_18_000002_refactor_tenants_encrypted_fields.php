<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Keep original columns for business display (non-sensitive use)
            // Add encrypted columns for sensitive data
            $table->text('document_encrypted')->nullable()->after('document');
            $table->string('document_hash', 64)->nullable()->after('document_encrypted');
            $table->text('phone_encrypted')->nullable()->after('phone');
            $table->string('phone_hash', 64)->nullable()->after('phone_encrypted');

            $table->index('document_hash');
            $table->index('phone_hash');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'document_encrypted',
                'document_hash',
                'phone_encrypted',
                'phone_hash',
            ]);
        });
    }
};