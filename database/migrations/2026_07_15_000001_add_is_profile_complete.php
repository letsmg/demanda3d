<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clients
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('is_profile_complete')->default(false)->after('password');
        });

        // Carriers
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('is_profile_complete')->default(false)->after('website_url');
        });

        // Tenants (Vendedores)
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('is_profile_complete')->default(false)->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('is_profile_complete');
        });
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn('is_profile_complete');
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('is_profile_complete');
        });
    }
};