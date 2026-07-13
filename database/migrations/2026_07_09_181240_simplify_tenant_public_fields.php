<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Descriptografa dados existentes para texto puro antes de remover colunas
        $tenants = DB::table('tenants')
            ->whereNotNull('fantasy_name_encrypted')
            ->orWhereNotNull('document_encrypted')
            ->orWhereNotNull('phone_encrypted')
            ->orWhereNotNull('address_encrypted')
            ->orWhereNotNull('number_encrypted')
            ->orWhereNotNull('district_encrypted')
            ->orWhereNotNull('city_encrypted')
            ->get();

        foreach ($tenants as $tenant) {
            $updates = [];

            foreach (['fantasy_name', 'document', 'phone', 'address', 'number', 'district', 'city'] as $field) {
                $encrypted = $tenant->{$field . '_encrypted'} ?? null;
                if ($encrypted) {
                    try {
                        $updates[$field] = \Illuminate\Support\Facades\Crypt::decryptString($encrypted);
                    } catch (\Exception) {
                        $updates[$field] = null;
                    }
                }
            }

            if (!empty($updates)) {
                DB::table('tenants')->where('id', $tenant->id)->update($updates);
            }
        }

        Schema::table('tenants', function (Blueprint $table) {
            // Adiciona colunas públicas (texto puro)
            $table->string('fantasy_name', 255)->nullable()->after('fantasy_name_hash');
            $table->string('document', 18)->nullable()->after('document_hash');
            $table->string('phone', 20)->nullable()->after('phone_hash');
            $table->string('address', 255)->nullable()->after('address_hash');
            $table->string('number', 20)->nullable()->after('number_hash');
            $table->string('district', 100)->nullable()->after('district_hash');
            $table->string('city', 100)->nullable()->after('city_hash');

            // Remove colunas criptografadas (exceto company_name que permanece LGPD)
            $table->dropColumn([
                'fantasy_name_encrypted',
                'fantasy_name_hash',
                'document_encrypted',
                'document_hash',
                'phone_encrypted',
                'phone_hash',
                'address_encrypted',
                'address_hash',
                'number_encrypted',
                'number_hash',
                'district_encrypted',
                'district_hash',
                'city_encrypted',
                'city_hash',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('fantasy_name_encrypted')->nullable()->after('fantasy_name');
            $table->string('fantasy_name_hash', 64)->nullable()->after('fantasy_name_encrypted');
            $table->text('document_encrypted')->nullable()->after('fantasy_name_hash');
            $table->string('document_hash', 64)->nullable()->after('document_encrypted');
            $table->text('phone_encrypted')->nullable()->after('document_hash');
            $table->string('phone_hash', 64)->nullable()->after('phone_encrypted');
            $table->text('address_encrypted')->nullable()->after('phone_hash');
            $table->string('address_hash', 64)->nullable()->after('address_encrypted');
            $table->text('number_encrypted')->nullable()->after('address_hash');
            $table->string('number_hash', 64)->nullable()->after('number_encrypted');
            $table->text('district_encrypted')->nullable()->after('number_hash');
            $table->string('district_hash', 64)->nullable()->after('district_encrypted');
            $table->text('city_encrypted')->nullable()->after('district_hash');
            $table->string('city_hash', 64)->nullable()->after('city_encrypted');

            $table->dropColumn([
                'fantasy_name',
                'document',
                'phone',
                'address',
                'number',
                'district',
                'city',
            ]);
        });
    }
};