<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 255)->after('name')->nullable();
            $table->string('meta_title', 120)->nullable()->after('extras_cost');
            $table->string('meta_description', 320)->nullable()->after('meta_title');
            $table->string('canonical_url', 255)->nullable()->after('meta_description');
            $table->string('og_image', 255)->nullable()->after('canonical_url');
        });

        // Índices aplicados separadamente para compatibilidade com SQLite
        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['slug']);
            $table->dropColumn(['slug', 'meta_title', 'meta_description', 'canonical_url', 'og_image']);
        });
    }
};