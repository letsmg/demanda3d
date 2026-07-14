<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrier_tenant_agreements', function (Blueprint $table) {
            $table->string('blocked_by', 20)->nullable()->after('status')
                ->comment('Quem bloqueou: seller ou carrier');
        });
    }

    public function down(): void
    {
        Schema::table('carrier_tenant_agreements', function (Blueprint $table) {
            $table->dropColumn('blocked_by');
        });
    }
};