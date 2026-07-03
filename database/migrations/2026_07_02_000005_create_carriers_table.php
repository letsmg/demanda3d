<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->date('data_nascimento')->nullable()->after('name');
            $table->string('doc_type', 4)->default('CNPJ');
            $table->string('ie', 20)->nullable();

            // Documento (paridade)
            $table->text('document_encrypted');
            $table->string('document_hash', 64);
            $table->unique(['tenant_id', 'document_hash']);

            // Endereço (paridade)
            $table->text('address_encrypted')->nullable();
            $table->string('address_hash', 64)->nullable();
            $table->text('number_encrypted')->nullable();
            $table->string('number_hash', 64)->nullable();
            $table->text('district_encrypted')->nullable();
            $table->string('district_hash', 64)->nullable();
            $table->text('city_encrypted')->nullable();
            $table->string('city_hash', 64)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode', 9)->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();

            // Contato
            $table->text('contact1_encrypted')->nullable();
            $table->string('contact1_hash', 64)->nullable();
            $table->text('phone1_encrypted')->nullable();
            $table->string('phone1_hash', 64)->nullable();
            $table->text('contact2_encrypted')->nullable();
            $table->string('contact2_hash', 64)->nullable();
            $table->text('phone2_encrypted')->nullable();
            $table->string('phone2_hash', 64)->nullable();

            // Auth
            $table->string('email', 255)->nullable();
            $table->string('password')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->rememberToken()->after('email_verified_at');

            // Dados públicos + bloqueio
            $table->string('website', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->text('blocked_reason')->nullable();

            $table->timestamps();
            $table->index('tenant_id');
            $table->index('doc_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};