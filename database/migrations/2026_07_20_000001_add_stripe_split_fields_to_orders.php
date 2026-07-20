<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->unique()->after('stripe_session_id');
            $table->string('stripe_transfer_seller_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('stripe_transfer_carrier_id')->nullable()->after('stripe_transfer_seller_id');
            $table->decimal('platform_fee_amount', 12, 2)->nullable()->after('stripe_transfer_carrier_id');
            $table->decimal('seller_amount', 12, 2)->nullable()->after('platform_fee_amount');
            $table->decimal('carrier_amount', 12, 2)->nullable()->after('seller_amount');
            $table->string('payment_split_status')->default('pending')->after('carrier_amount');

            $table->index('payment_split_status');
            $table->index('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_payment_intent_id',
                'stripe_transfer_seller_id',
                'stripe_transfer_carrier_id',
                'platform_fee_amount',
                'seller_amount',
                'carrier_amount',
                'payment_split_status',
            ]);
        });
    }
};