<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de solicitações de devolução.
     *
     * Fluxo:
     * 1. Cliente solicita devolução até 7 dias após recebimento → status = 'requested'
     * 2. Cliente posta o produto em até 3 dias → status = 'shipped_back'
     * 3. Vendedor recebe e confirma → status = 'approved' (reembolso liberado)
     */
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('status', 20)->default('requested'); // requested | shipped_back | approved | rejected
            $table->text('reason_encrypted')->nullable();
            $table->string('reason_hash', 64)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('shipped_back_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason_encrypted')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index('status');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};