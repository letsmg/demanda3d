<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela que armazena versões dos documentos legais (Termos de Uso e Política de Privacidade).
     *
     * Cada nova versão gera um novo registro, mantendo histórico completo.
     * O campo `type` diferencia 'terms_of_service' de 'privacy_policy'.
     */
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->comment('terms_of_service, privacy_policy');
            $table->unsignedInteger('version')->default(1);
            $table->string('title', 255);
            $table->text('content_html')->comment('Conteúdo HTML do documento');
            $table->timestamp('published_at')->nullable()->comment('Data de publicação/ativação');
            $table->timestamp('archived_at')->nullable()->comment('Data de arquivamento (versão substituída)');
            $table->timestamps();

            // Uma única versão ativa por tipo
            $table->unique(['type', 'version']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};