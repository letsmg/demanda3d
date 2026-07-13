<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela pivot: carrier_tenant_agreements
     *
     * Gerencia acordos comerciais entre tenants (vendedores) e carriers (transportadoras).
     *
     * Fluxo de status:
     *   - pending_tenant: transportador solicitou conexão, aguardando aprovação do tenant
     *   - pending_carrier: tenant convidou transportador, aguardando aceitação do carrier
     *   - active: acordo ativo, transportador pode operar fretes para o tenant
     *   - rejected: convite/solicitação rejeitado
     */
    public function up(): void
    {
        Schema::create('carrier_tenant_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->string('status', 20)->default('pending_carrier')
                ->comment('pending_tenant, pending_carrier, active, rejected');
            $table->timestamps();

            $table->unique(['tenant_id', 'carrier_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_tenant_agreements');
    }
};