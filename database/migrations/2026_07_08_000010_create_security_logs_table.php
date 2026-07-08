<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela security_logs para auditoria de tentativas de upload
     * com conteúdo violador (adulto, violência, etc.) detectado via Google Cloud Vision.
     *
     * Visível apenas para administradores (access_level = 10) no dashboard.
     */
    public function up(): void
    {
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('attempted_at');
            $table->string('violation_type')->comment('ADULT, VIOLENCE, RACY, MEDICAL');
            $table->json('raw_response')->nullable()->comment('Resposta completa da API Google Cloud Vision SafeSearch');

            $table->index('attempted_at');
            $table->index('violation_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};