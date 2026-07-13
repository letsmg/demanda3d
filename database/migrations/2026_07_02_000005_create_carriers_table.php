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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Company name (LGPD: encrypted + hash)
            $table->text('company_name_encrypted')->nullable();
            $table->string('company_name_hash', 64)->nullable();

            // Fantasy name + slug (público)
            $table->string('fantasy_name', 255);
            $table->string('slug', 255)->nullable()->unique();

            // Document type + document (LGPD: encrypted + hash)
            $table->string('document_type', 4)->default('cnpj')->comment('cnpj ou cpf');
            $table->text('document_encrypted');
            $table->string('document_hash', 64);

            // Address (LGPD: encrypted)
            $table->text('address_encrypted')->nullable();

            // Phone (LGPD: encrypted)
            $table->text('phone_encrypted')->nullable();

            // Public profile
            $table->string('logo_path', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Índices
            $table->index('user_id');
            $table->index('fantasy_name');
            $table->index('slug');
            $table->index('document_type');
            $table->index('company_name_hash');
            $table->index('document_hash');
            $table->index('is_active');
            $table->index('rating_average');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};