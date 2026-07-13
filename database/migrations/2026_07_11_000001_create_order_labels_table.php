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
        Schema::create('order_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('carrier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('tracking_code')->nullable()->comment('Código de rastreio da transportadora');
            $table->string('label_url')->nullable()->comment('URL do PDF/HTML da etiqueta');
            $table->string('status')->default('pending')->comment('pending, generated, shipped, delivered');

            // Dados exibidos na etiqueta (não sensíveis — dados de envio visíveis)
            $table->string('recipient_name')->comment('Nome do destinatário (display_name)');
            $table->text('recipient_address')->comment('Endereço completo de entrega');

            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_labels');
    }
};