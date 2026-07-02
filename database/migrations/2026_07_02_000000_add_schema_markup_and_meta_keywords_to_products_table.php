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
            $table->string('meta_keywords', 255)->nullable()->after('meta_description');
            // IMPORTANT: schema_markup contains JSON-LD structured data and MUST NOT be sanitized
            // This field accepts valid JSON/HTML for SEO purposes
            $table->text('schema_markup')->nullable()->after('og_image');
            // IMPORTANT: google_tag_manager contains GTM scripts and MUST NOT be sanitized
            // This field accepts valid HTML/JS for tracking purposes
            $table->text('google_tag_manager')->nullable()->after('schema_markup');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['meta_keywords', 'schema_markup', 'google_tag_manager']);
        });
    }
};