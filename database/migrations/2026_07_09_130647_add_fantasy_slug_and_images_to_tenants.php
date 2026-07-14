<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('fantasy_slug')->nullable()->unique()->after('fantasy_name_hash');
            $table->text('logo_path')->nullable()->after('zipcode');
            $table->text('banner_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['fantasy_slug', 'logo_path', 'banner_path']);
        });
    }
};