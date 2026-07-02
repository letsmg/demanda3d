<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de contratos de frete.
     *
     * Gerencia fretes contratados com transportadoras, vinculados
     * opcionalmente a pedidos (orders).
     */
    public function up(): void
    {
        Schema::create('freight_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('pickup_location', 500);
            $table->string('delivery_location', 500);
            $table->string('cargo_description', 500);

            $table->date('pickup_date');
            $table->date('estimated_delivery_date');
            $table->date('delivered_date')->nullable();
            $table->boolean('freight_paid')->default(false);
            $table->decimal('freight_value', 12, 2)->default(0);

            $table->string('status', 20)->default('pending')
                ->comment('pending, in_transit, delivered, cancelled');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('carrier_id');
            $table->index('order_id');
            $table->index('status');
            $table->index('pickup_date');
            $table->index('estimated_delivery_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_contracts');
    }
};