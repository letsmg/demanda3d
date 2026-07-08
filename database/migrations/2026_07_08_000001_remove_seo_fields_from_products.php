<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove meta_title, meta_description e meta_keywords da tabela products.
     *
     * Todos estes campos são 100% deriváveis dos campos nativos do produto:
     *   - meta_title       → name (máx. 120 chars)
     *   - meta_description → description (strip_tags, máx. 320 chars)
     *   - meta_keywords    → name + categorias + termos de nicho
     *   - canonical_url    → route('store.detail', slug) — nunca foi coluna
     *   - og_image         → primeira imagem do produto — nunca foi coluna
     *
     * A geração é feita via accessors no Model Product.
     */
    public function up(): void
    {
        Schema::table('products', function ($table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function ($table) {
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
        });
    }
};