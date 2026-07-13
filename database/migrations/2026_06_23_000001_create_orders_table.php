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
            $table->string('stripe_session_id')->nullable()->unique();
            $table->decimal('amount_total', 12, 2)->nullable();
            $table->string('currency', 3)->nullable()->default('brl');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['tenant_id', 'client_id']);
            $table->index('order_date');
            $table->index('delivery_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};