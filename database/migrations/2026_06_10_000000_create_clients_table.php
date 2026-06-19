<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('display_name', 255)->nullable();
            $table->string('doc');
            $table->text('doc_encrypted')->nullable();
            $table->string('doc_hash', 64)->nullable();
            $table->string('address');
            $table->string('number');
            $table->string('state');
            $table->string('zipcode');
            $table->string('city');
            $table->string('phone1');
            $table->text('phone1_encrypted')->nullable();
            $table->string('phone1_hash', 64)->nullable();
            $table->string('phone2');
            $table->text('phone2_encrypted')->nullable();
            $table->string('phone2_hash', 64)->nullable();
            $table->string('contact1')->nullable();
            $table->string('contact2')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('doc_hash');
            $table->index('phone1_hash');
            $table->index('phone2_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};