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
            $table->text('company_name_encrypted')->nullable();
            $table->string('company_name_hash', 64)->nullable();
            $table->text('fantasy_name_encrypted')->nullable();
            $table->string('fantasy_name_hash', 64)->nullable();
            $table->text('document_encrypted')->nullable();
            $table->string('document_hash', 64)->nullable();
            $table->text('phone_encrypted')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->text('address_encrypted')->nullable();
            $table->string('address_hash', 64)->nullable();
            $table->text('number_encrypted')->nullable();
            $table->string('number_hash', 64)->nullable();
            $table->text('district_encrypted')->nullable();
            $table->string('district_hash', 64)->nullable();
            $table->text('city_encrypted')->nullable();
            $table->string('city_hash', 64)->nullable();
            $table->string('state', 2);
            $table->string('zipcode', 10);
            $table->boolean('active')->default(true);
            $table->decimal('rating_average', 3, 2)->default(0)->comment('Nota média consolidada (ex: 4.85)');
            $table->integer('rating_count')->default(0)->comment('Total de avaliações recebidas');
            $table->timestamps();

            $table->index('user_id');
            $table->index('company_name_hash');
            $table->index('fantasy_name_hash');
            $table->index('document_hash');
            $table->index('phone_hash');
            $table->index('address_hash');
            $table->index('number_hash');
            $table->index('district_hash');
            $table->index('city_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};