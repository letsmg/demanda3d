<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('is_active');
            $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            $table->text('blocked_reason')->nullable()->after('blocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('carriers', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'blocked_at', 'blocked_reason']);
        });
    }
};