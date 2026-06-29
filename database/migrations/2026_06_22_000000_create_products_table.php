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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('height')->nullable()->comment('Altura em mm');
            $table->integer('width')->nullable()->comment('Largura em mm');
            $table->integer('approximate_weight')->nullable()->comment('Peso final da peça em gramas');
            $table->integer('waste_weight')->nullable()->comment('Peso da purga/suportes/perdas em gramas');
            $table->string('material_type')->nullable()->comment('Tipo: filament ou resin');
            $table->integer('print_time')->nullable()->comment('Tempo de impressão em minutos');
            $table->integer('pieces_produced')->nullable()->comment('Quantidade de peças rendidas na fornada');
            $table->decimal('maintenance_fee', 12, 2)->nullable()->comment('Taxa de desgaste da máquina');
            $table->integer('painting_time')->nullable()->comment('Tempo de pintura em minutos (opcional)');
            $table->string('painting_material')->nullable()->comment('Materiais usados na pintura');
            $table->decimal('painting_cost', 12, 2)->nullable()->comment('Custo dos materiais de pintura (opcional)');
            $table->decimal('extras_cost', 12, 2)->nullable()->comment('Custo de embalagem, LEDs, argolas, etc');
            $table->decimal('approximate_cost', 12, 2)->nullable()->comment('Custo total calculado');
            $table->decimal('sale_price', 12, 2)->comment('Valor final de venda');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};