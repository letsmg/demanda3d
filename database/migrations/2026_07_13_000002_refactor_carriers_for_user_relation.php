<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refatora a tabela carriers para o novo modelo B2B:
     * - Adiciona user_id (1:1 com users) como substituto da autenticação própria
     * - Remove colunas de auth duplicadas (email, password, email_verified_at, remember_token)
     * - Remove tenant_id (carriers agora são usuários globais do sistema, não vinculados a um único tenant)
     * - Os dados cadastrais permanecem na tabela carriers (perfil público do transportador)
     */
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            // 1. Adiciona user_id (nullable inicialmente para compatibilidade)
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();

            // 2. Remove colunas de autenticação (agora delegadas à tabela users)
            $table->dropColumn(['email', 'password', 'email_verified_at', 'remember_token']);
        });

        // 3. Remove tenant_id (transportador é usuário global, não pertence a um único tenant)
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email', 255)->nullable();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};