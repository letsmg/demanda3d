<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove a coluna purchase_date da tabela inputs.
     *
     * A data de compra agora reside na tabela accounts_payable,
     * que é o local semanticamente correto para dados de transação financeira.
     * A tabela inputs descreve APENAS o insumo (material, quantidade, custo).
     */
    public function up(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->dropColumn('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::table('inputs', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('brand');
        });
    }
};