<?php

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona UNIQUE(uf, cep_start, cep_end) para que insertOrIgnore()
     * no PostgreSQL possa identificar duplicatas corretamente.
     */
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->unique(['uf', 'cep_start', 'cep_end'], 'states_uf_cep_unique');
        });
    }

    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique('states_uf_cep_unique');
        });
    }
};