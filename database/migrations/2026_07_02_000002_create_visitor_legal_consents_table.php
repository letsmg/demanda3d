<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela que registra aceites e recusas dos documentos legais.
     *
     * Estrutura de paridade (LGPD):
     * - ip_hash: hash determinístico para buscas e UNIQUE INDEX
     * - ip_encrypted: criptografia em repouso para auditoria
     *
     * Um visitante pode RECUSAR o aceite (nao precisa estar logado).
     * Mas para se CADASTRAR como cliente ou usuário, o aceite é OBRIGATÓRIO.
     */
    public function up(): void
    {
        Schema::create('visitor_legal_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legal_document_id');
            $table->foreign('legal_document_id')->references('id')->on('legal_documents')->cascadeOnDelete();

            // Consentimento: accepted, declined
            $table->string('status', 20)->default('accepted')->comment('accepted, declined');

            // Paridade de IP
            $table->string('ip_hash', 64)->comment('hash(sha256, $ip) para buscas e índices');
            $table->text('ip_encrypted')->comment('Crypt::encryptString($ip) para auditoria');

            // Metadados do visitante (texto puro permitido)
            $table->string('user_agent', 512)->nullable();
            $table->string('geolocation', 100)->nullable()->comment('País/Cidade aproximada');

            // FK opcional para clientes cadastrados
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();

            // FK opcional para usuários staff cadastrados
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            // Índices para performance
            $table->index('ip_hash');
            $table->index('status');
            $table->index('client_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_legal_consents');
    }
};