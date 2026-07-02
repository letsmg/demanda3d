<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona unique constraint no email da tabela clients.
     *
     * O email é a ÚNICA exceção de texto puro no sistema (exigência do Laravel Fortify).
     * Mesmo assim, precisa de unique constraint para integridade + defesa em profundidade.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }
};