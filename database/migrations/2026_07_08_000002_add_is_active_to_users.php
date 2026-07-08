<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona a coluna is_active à tabela users para controle de ativação/bloqueio.
     *
     * Admin pode ativar ou bloquear vendedores (staff) com um clique.
     * Admins (access_level = 10) não podem ser bloqueados.
     */
    public function up(): void
    {
        Schema::table('users', function ($table) {
            $table->boolean('is_active')->default(true)->after('access_level');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('is_active');
        });
    }
};