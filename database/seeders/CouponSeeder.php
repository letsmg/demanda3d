<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'BEMVINDO10'],
            [
                'tenant_id'       => null,
                'type'            => 'percentage',
                'value'           => 10.00,
                'min_order_value' => 50.00,
                'max_uses'        => 100,
                'used_count'      => 0,
                'starts_at'       => now(),
                'expires_at'      => now()->addMonths(6),
                'is_active'       => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'FRETE50'],
            [
                'tenant_id'       => null,
                'type'            => 'fixed',
                'value'           => 50.00,
                'min_order_value' => 200.00,
                'max_uses'        => 50,
                'used_count'      => 0,
                'starts_at'       => now(),
                'expires_at'      => now()->addMonths(3),
                'is_active'       => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'CATEGORIA20'],
            [
                'tenant_id'       => null,
                'type'            => 'percentage',
                'value'           => 20.00,
                'min_order_value' => 30.00,
                'max_uses'        => 30,
                'used_count'      => 0,
                'starts_at'       => now(),
                'expires_at'      => now()->addMonths(3),
                'is_active'       => true,
            ]
        );
    }
}