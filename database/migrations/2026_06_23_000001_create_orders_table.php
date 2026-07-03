<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Orders dependem de: tenants, clients, products.
     * Timestamp 2026_06_23_000001 garante execução após products (2026_06_22_000000)
     * e antes de carts (2026_06_23_000000? ajustado para 000001).
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('delivery_date');
            $table->decimal('price', 12, 2);
            $table->text('contracted_description_encrypted')->nullable();
            $table->string('contracted_description_hash', 64)->nullable();
            $table->string('stripe_session_id')->nullable()->unique();
            $table->decimal('amount_total', 12, 2)->nullable();
            $table->string('currency', 3)->nullable()->default('brl');
            $table->string('status')->default('pending');
            $table->timestamps();

            // Índices para busca por tenant + cliente e tenant + produto
            $table->index(['tenant_id', 'client_id']);
            $table->index(['tenant_id', 'product_id']);
            $table->index('order_date');
            $table->index('delivery_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};