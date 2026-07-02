<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela de contas a pagar.
     *
     * Relaciona-se com suppliers (obrigatório) e inputs (opcional).
     * A data de compra (purchase_date) reside aqui, não na tabela inputs.
     */
    public function up(): void
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('input_id')->nullable()->constrained('inputs')->nullOnDelete();

            $table->string('description', 255);
            $table->date('purchase_date');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('pending')
                ->comment('pending, partially_paid, paid, overdue, cancelled');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('supplier_id');
            $table->index('input_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_payable');
    }
};