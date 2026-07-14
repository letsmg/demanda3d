<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('bank_name', 100)->nullable()->comment('Nome do banco (texto puro)');

            // Campos sensíveis — criptografados em repouso
            $table->text('routing_number_encrypted')->nullable()->comment('Agência bancária (encrypted)');
            $table->text('account_number_encrypted')->nullable()->comment('Número da conta (encrypted)');
            $table->text('bank_pix_key_encrypted')->nullable()->comment('Chave PIX (encrypted)');

            // Titular (LGPD: hash + encrypted)
            $table->string('account_holder_name', 255)->nullable()->comment('Nome do titular');
            $table->text('account_holder_doc_encrypted')->nullable();
            $table->string('account_holder_doc_hash', 64)->nullable();

            // Consentimento LGPD (auditoria)
            $table->boolean('consented')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->string('consent_ip', 45)->nullable();
            $table->string('consent_term_version', 20)->nullable()->default('1.0');

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('account_holder_doc_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};