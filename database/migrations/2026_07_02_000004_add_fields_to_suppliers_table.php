<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expande a tabela suppliers com endereço, contatos, IE, site, observações.
     *
     * Estrutura de paridade (LGPD): campos *_encrypted + *_hash para dados sensíveis.
     * Exceções em texto puro: state, zipcode, email, website, notes, doc_type, ie, is_active.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Tipo de documento e IE
            $table->string('doc_type', 4)->default('CNPJ')->after('name');
            $table->string('ie', 20)->nullable()->after('doc_type');

            // Endereço (paridade)
            $table->text('address_encrypted')->nullable()->after('contact_encrypted');
            $table->string('address_hash', 64)->nullable()->after('address_encrypted');
            $table->text('number_encrypted')->nullable()->after('address_hash');
            $table->string('number_hash', 64)->nullable()->after('number_encrypted');
            $table->text('district_encrypted')->nullable()->after('number_hash');
            $table->string('district_hash', 64)->nullable()->after('district_encrypted');
            $table->text('city_encrypted')->nullable()->after('district_hash');
            $table->string('city_hash', 64)->nullable()->after('city_encrypted');

            // State e zipcode (texto puro — baixa sensibilidade)
            $table->string('state', 2)->nullable()->after('city_hash');
            $table->string('zipcode', 9)->nullable()->after('state');

            // Contato 1
            $table->text('contact1_encrypted')->nullable()->after('zipcode');
            $table->string('contact1_hash', 64)->nullable()->after('contact1_encrypted');
            $table->text('phone1_encrypted')->nullable()->after('contact1_hash');
            $table->string('phone1_hash', 64)->nullable()->after('phone1_encrypted');

            // Contato 2
            $table->text('contact2_encrypted')->nullable()->after('phone1_hash');
            $table->string('contact2_hash', 64)->nullable()->after('contact2_encrypted');
            $table->text('phone2_encrypted')->nullable()->after('contact2_hash');
            $table->string('phone2_hash', 64)->nullable()->after('phone2_encrypted');

            // Dados públicos
            $table->string('email', 255)->nullable()->after('phone2_hash');
            $table->string('website', 255)->nullable()->after('email');
            $table->text('notes')->nullable()->after('website');
            $table->boolean('is_active')->default(true)->after('notes');

            // Índices
            $table->index('doc_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'doc_type', 'ie',
                'address_encrypted', 'address_hash',
                'number_encrypted', 'number_hash',
                'district_encrypted', 'district_hash',
                'city_encrypted', 'city_hash',
                'state', 'zipcode',
                'contact1_encrypted', 'contact1_hash',
                'phone1_encrypted', 'phone1_hash',
                'contact2_encrypted', 'contact2_hash',
                'phone2_encrypted', 'phone2_hash',
                'email', 'website', 'notes', 'is_active',
            ]);
        });
    }
};