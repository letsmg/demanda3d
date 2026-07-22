<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona colunas de geolocalização (latitude/longitude) à tabela tenants
     * para habilitar busca de lojas por proximidade geográfica (Local-First).
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('zipcode');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        // Índice composto para consultas geoespaciais de proximidade
        DB::statement('CREATE INDEX IF NOT EXISTS tenants_geo_idx ON tenants (latitude, longitude)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('tenants_geo_idx');
            $table->dropColumn(['longitude', 'latitude']);
        });
    }
};