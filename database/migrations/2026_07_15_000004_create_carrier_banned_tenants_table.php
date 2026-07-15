<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrier_banned_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['carrier_id', 'tenant_id']);
            $table->index('carrier_id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrier_banned_tenants');
    }
};