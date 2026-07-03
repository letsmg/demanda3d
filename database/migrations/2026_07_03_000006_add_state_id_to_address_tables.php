<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona state_id (FK → states) nas tabelas com endereço.
     *
     * O state_id permite preencher automaticamente o estado a partir
     * do CEP digitado, consultando a tabela states.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('city_hash')
                ->constrained('states')->nullOnDelete();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('city_hash')
                ->constrained('states')->nullOnDelete();
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->foreignId('state_id')->nullable()->after('city_hash')
                ->constrained('states')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
        });
    }
};