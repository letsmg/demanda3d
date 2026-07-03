<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de estados com faixas de CEP oficiais dos Correios.
     * Usada para mapear regiões de atuação das transportadoras.
     */
    public function up(): void
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('uf', 2);
            $table->string('name', 100);
            $table->string('cep_start', 9);
            $table->string('cep_end', 9);
            $table->timestamps();

            $table->index('uf');
            $table->index(['cep_start', 'cep_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};