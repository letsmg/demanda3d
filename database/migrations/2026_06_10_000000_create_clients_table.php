<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('display_name', 255)->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('doc_type', 4)->default('CPF');
            $table->text('first_name_encrypted')->nullable();
            $table->string('first_name_hash', 64)->nullable();
            $table->text('last_name_encrypted')->nullable();
            $table->string('last_name_hash', 64)->nullable();
            $table->text('doc_encrypted')->nullable();
            $table->string('doc_hash', 64)->nullable();
            $table->text('address_encrypted')->nullable();
            $table->string('address_hash', 64)->nullable();
            $table->text('number_encrypted')->nullable();
            $table->string('number_hash', 64)->nullable();
            $table->text('state_encrypted')->nullable();
            $table->string('state_hash', 64)->nullable();
            $table->text('zipcode_encrypted')->nullable();
            $table->string('zipcode_hash', 64)->nullable();
            $table->text('city_encrypted')->nullable();
            $table->string('city_hash', 64)->nullable();
            $table->text('phone1_encrypted')->nullable();
            $table->string('phone1_hash', 64)->nullable();
            $table->text('phone2_encrypted')->nullable();
            $table->string('phone2_hash', 64)->nullable();
            $table->text('contact1_encrypted')->nullable();
            $table->string('contact1_hash', 64)->nullable();
            $table->text('contact2_encrypted')->nullable();
            $table->string('contact2_hash', 64)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('first_name_hash');
            $table->index('last_name_hash');
            $table->index('doc_type');
            $table->index('doc_hash');
            $table->index('address_hash');
            $table->index('number_hash');
            $table->index('state_hash');
            $table->index('zipcode_hash');
            $table->index('city_hash');
            $table->index('phone1_hash');
            $table->index('phone2_hash');
            $table->index('contact1_hash');
            $table->index('contact2_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};