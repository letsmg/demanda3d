<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refatora a tabela orders para suportar múltiplos itens por pedido.
     *
     * Remove:
     *   - product_id (agora em order_items)
     *   - price (agora em order_items.snapshot_product_price)
     *   - contracted_description_encrypted / contracted_description_hash (agora em order_items)
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key e índice antes de remover a coluna
            $table->dropForeign(['product_id']);
            $table->dropIndex(['tenant_id', 'product_id']);

            $table->dropColumn([
                'product_id',
                'price',
                'contracted_description_encrypted',
                'contracted_description_hash',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2)->nullable();
            $table->text('contracted_description_encrypted')->nullable();
            $table->string('contracted_description_hash', 64)->nullable();
            $table->index(['tenant_id', 'product_id']);
        });
    }
};