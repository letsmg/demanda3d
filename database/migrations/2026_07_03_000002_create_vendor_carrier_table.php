<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table: vendor_carrier
     *
     * Gerencia o vínculo entre vendedores (users staff) e transportadoras (carriers).
     * O status controla o fluxo de aprovação:
     *   - pending: transportadora ainda não aceitou
     *   - approved: vínculo confirmado, produtos podem exibir opções de frete
     *   - rejected: transportadora recusou o vínculo
     */
    public function up(): void
    {
        Schema::create('vendor_carrier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'carrier_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_carrier');
    }
};