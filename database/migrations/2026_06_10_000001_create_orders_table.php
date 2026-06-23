<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('delivery_date');
            $table->decimal('price', 12, 2);
            $table->text('contracted_description_encrypted')->nullable();
            $table->string('contracted_description_hash', 64)->nullable();
            $table->string('stripe_session_id')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('client_id');
            $table->index('stripe_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};