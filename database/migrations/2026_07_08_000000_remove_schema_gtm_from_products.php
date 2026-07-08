<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove schema_markup e google_tag_manager da tabela products.
     *
     * Ambos são gerados dinamicamente via accessors no Model Product.
     */
    public function up(): void
    {
        Schema::table('products', function ($table) {
            $table->dropColumn(['schema_markup', 'google_tag_manager']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function ($table) {
            $table->text('schema_markup')->nullable();
            $table->text('google_tag_manager')->nullable();
        });
    }
};