<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela: order_items
     *
     * Armazena o snapshot imutável de cada item do pedido no momento da compra.
     * Garante que alterações futuras no produto (nome, preço) ou sua exclusão
     * (soft delete) não afetem o histórico do pedido.
     *
     * Colunas:
     *   - snapshot_product_name: nome do produto no momento da compra
     *   - snapshot_product_price: preço unitário no momento da compra (decimal 12,2)
     *   - quantity: quantidade comprada
     *   - product_id: FK nullable — preserva referência, mas sobrevive a soft deletes
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('snapshot_product_name', 500);
            $table->decimal('snapshot_product_price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};