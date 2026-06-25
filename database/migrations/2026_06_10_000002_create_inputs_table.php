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
        Schema::create('inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('brand');
            $table->date('purchase_date');
            $table->integer('quantity')->comment('Gramas ou unidades');
            $table->decimal('shipping_cost', 12, 2)->comment('Valor do frete rateado');
            $table->decimal('cost_value', 12, 2)->comment('Valor pago no insumo');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inputs');
    }
};