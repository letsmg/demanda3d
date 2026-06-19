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
            $table->string('company_name');
            $table->string('fantasy_name')->nullable();
            $table->string('document', 20)->unique();
            $table->text('document_encrypted')->nullable();
            $table->string('document_hash', 64)->nullable();
            $table->string('phone', 20);
            $table->text('phone_encrypted')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->string('address');
            $table->string('number', 20);
            $table->string('district');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zipcode', 10);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('document_hash');
            $table->index('phone_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};