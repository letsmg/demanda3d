<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Bloqueia ativação de produto sem transportadora vinculada ao vendedor.
     */
    public function saving(Product $product): void
    {
        // Só valida em produção. Em desenvolvimento/local, permite criar sem transportadora.
        if (! app()->isProduction()) {
            return;
        }

        if ($product->is_active) {
            $tenant = $product->tenant;
            if ($tenant) {
                $hasCarrier = \App\Models\VendorCarrier::where('user_id', $tenant->user_id)
                    ->where('status', 'approved')
                    ->exists();

                if (! $hasCarrier) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], [], [], []),
                        response()->json([
                            'message' => 'Não é possível ativar um produto sem transportadora vinculada ao vendedor.',
                        ], 422),
                    );
                }
            }
        }
    }
}