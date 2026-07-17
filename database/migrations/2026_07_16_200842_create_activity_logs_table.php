<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // ── Multi-tenant ──────────────────────────────────────
            // Nulo para ações globais de ADMIN (ex: bloquear usuário).
            // Preenchido para ações dentro do escopo de um tenant.
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            // ── Quem executou a ação (causer polimórfico) ──────────
            // Pode ser um User (admin, seller, carrier) ou um Client.
            $table->nullableMorphs('causer');

            // ── Tipo da ação (human-readable) ──────────────────────
            // Ex: "Criou Produto", "Bloqueou Usuário", "Atualizou Pedido"
            $table->string('event', 200)->index();

            // ── Recurso afetado (subject polimórfico) ──────────────
            // Pode ser Product, Order, User, Tenant, etc.
            $table->nullableMorphs('subject');

            // ── Descrição textual legível ──────────────────────────
            // Ex: "João (Vendedor) atualizou o preço do produto 'Teclado' de R$ 300 para R$ 250"
            $table->text('description')->nullable();

            // ── Payload JSONB (dados estruturados da mudança) ───────
            // Armazena 'old' e 'attributes' (estado anterior e novo)
            // Ex: {"old": {"sale_price": 300.00}, "attributes": {"sale_price": 250.00}}
            $table->jsonb('properties')->nullable();

            // ── Timestamps ─────────────────────────────────────────
            $table->timestamp('created_at')->useCurrent()->index();

            // ── Índices compostos para performance de busca ────────
            // Busca por tenant + data (filtro mais comum)
            $table->index(['tenant_id', 'created_at']);
            // Busca por causer + data
            // (nullableMorphs já criou índices individuais para causer_type+causer_id
            //  e subject_type+subject_id; este índice adicional acelera filtro por data)
            $table->index(['causer_type', 'causer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};