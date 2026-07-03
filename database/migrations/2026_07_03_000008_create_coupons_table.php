<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de cupons de desconto.
     *
     * - Admin: tenant_id = null → vale para todas as lojas
     * - Vendedor: tenant_id definido → vale apenas para aquela loja
     * - category_id opcional → restringe a categoria de produtos
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('type', 20)->default('percentage'); // percentage | fixed
            $table->decimal('value', 12, 2);
            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};