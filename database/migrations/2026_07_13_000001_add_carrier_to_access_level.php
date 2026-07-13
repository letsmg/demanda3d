<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Atualiza o comentário da coluna access_level para refletir os novos níveis.
     *
     * Novo mapeamento:
     *   1=SELLER_1 (Vendedor Master), 2=SELLER_2 (Operacional),
     *   5=CARRIER_1 (Transportador Admin), 6=CARRIER_2 (Motorista),
     *   10=ADMIN, 15=CUSTOMER
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_level')
                ->default(15)
                ->comment('1=SELLER_1,2=SELLER_2,5=CARRIER_1,6=CARRIER_2,10=ADMIN,15=CUSTOMER')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('access_level')
                ->default(5)
                ->comment('0=operational,1=management,5=customer,10=admin')
                ->change();
        });
    }
};