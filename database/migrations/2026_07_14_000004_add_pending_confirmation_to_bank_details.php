<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->string('pending_token', 64)->nullable()->after('consent_term_version');
            $table->text('pending_data')->nullable()->after('pending_token');
            $table->timestamp('pending_at')->nullable()->after('pending_data');
        });
    }

    public function down(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {
            $table->dropColumn(['pending_token', 'pending_data', 'pending_at']);
        });
    }
};