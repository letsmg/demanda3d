<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Altera a coluna 'document' da tabela 'tenants' para VARCHAR(20)
     * a fim de suportar CNPJ alfanumérico (até 14 caracteres + formatação).
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('document', 20)->nullable()->change();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->text('document_encrypted')->change();
        });

        Schema::table('carriers', function (Blueprint $table) {
            $table->text('document_encrypted')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('document', 18)->nullable()->change();
        });
    }
};