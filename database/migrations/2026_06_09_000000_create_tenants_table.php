<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Company name (LGPD: encrypted + hash — única exceção operacional)
            $table->text('company_name_encrypted')->nullable();
            $table->string('company_name_hash', 64)->nullable();

            // Fantasy name + slug (público, NOT NULL via booted no Model)
            $table->string('fantasy_name', 255)->nullable();
            $table->string('fantasy_slug')->unique();

            // Document type + document (texto puro)
            $table->string('document_type', 4)->default('cnpj')->comment('cnpj ou cpf');
            $table->string('document', 18)->nullable();

            // Phone (texto puro)
            $table->string('phone', 20)->nullable();

            // Address (texto puro)
            $table->string('address', 255)->nullable();

            // Address complement
            $table->string('number', 20)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zipcode', 10)->nullable();

            // Images
            $table->string('logo_path', 500)->nullable();
            $table->string('banner_path', 500)->nullable();

            // Status + ratings
            $table->boolean('active')->default(true);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);

            $table->timestamps();

            // Índices
            $table->index('user_id');
            $table->index('company_name_hash');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};