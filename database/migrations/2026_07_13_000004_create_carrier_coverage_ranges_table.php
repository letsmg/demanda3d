<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela: carrier_coverage_ranges
     *
     * Substitui a antiga carrier_state (pivot por estado) por um modelo de faixas de CEP,
     * oferecendo granularidade muito maior (bairros, cidades, regiões metropolitanas).
     *
     * Cada faixa possui:
     *   - title: nome descritivo (ex: "Grande São Paulo", "Interior RJ")
     *   - cep_start / cep_end: intervalo de CEP (8 caracteres numéricos)
     *
     * Query para verificar cobertura:
     *   WHERE cep_start <= :cep AND cep_end >= :cep
     */
    public function up(): void
    {
        Schema::create('carrier_coverage_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('cep_start', 8);
            $table->string('cep_end', 8);
            $table->timestamps();

            $table->index('carrier_id');
            $table->index(['cep_start', 'cep_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_coverage_ranges');
    }
};