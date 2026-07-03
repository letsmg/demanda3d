<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table: carrier_state
     *
     * Relacionamento muitos-para-muitos entre transportadoras e estados.
     * Cada transportadora pode atuar em múltiplos estados.
     */
    public function up(): void
    {
        Schema::create('carrier_state', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carrier_id', 'state_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_state');
    }
};