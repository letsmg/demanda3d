<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('status');
            $table->text('snapshot_address')->nullable()->after('delivered_at');
            $table->string('snapshot_product_name', 500)->nullable()->after('snapshot_address');
            $table->decimal('snapshot_product_price', 12, 2)->nullable()->after('snapshot_product_name');

            $table->index('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'snapshot_address', 'snapshot_product_name', 'snapshot_product_price']);
        });
    }
};