<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * POST /api/coupons/validate
     *
     * Valida e calcula o desconto de um cupom para os itens do carrinho.
     */
    public function check(Request $request): JsonResponse
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            return response()->json(['error' => 'Não autenticado.'], 401);
        }

        $request->validate(['code' => ['required', 'string', 'max:50']]);

        $coupon = Coupon::where('code', $request->input('code'))->first();

        if (! $coupon) {
            return response()->json(['error' => 'Cupom inválido ou expirado.'], 422);
        }

        $cartItems = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes();
        }])->where('client_id', $client->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Carrinho vazio.'], 422);
        }

        $total = $cartItems->sum(fn ($i) => (float) $i->product->sale_price * $i->quantity);

        if (! $coupon->isValid($total)) {
            return response()->json(['error' => 'Cupom inválido, expirado ou valor mínimo não atingido.'], 422);
        }

        // Filtra itens que o cupom se aplica
        $applicableItems = $cartItems->filter(function ($item) use ($coupon) {
            // Cupom de tenant específico: só aplica se o produto é daquele tenant
            if ($coupon->tenant_id !== null && $item->product->tenant_id !== $coupon->tenant_id) {
                return false;
            }
            // Cupom de categoria específica: só aplica se o produto pertence à categoria
            if ($coupon->category_id !== null) {
                $productCategories = $item->product->categories()->pluck('id')->toArray();
                if (! in_array($coupon->category_id, $productCategories)) {
                    return false;
                }
            }
            return true;
        });

        if ($applicableItems->isEmpty()) {
            return response()->json(['error' => 'Este cupom não se aplica aos produtos do seu carrinho.'], 422);
        }

        $applicableTotal = $applicableItems->sum(fn ($i) => (float) $i->product->sale_price * $i->quantity);
        $discountedTotal = $coupon->applyTo($applicableTotal);
        $discount = $applicableTotal - $discountedTotal;

        return response()->json([
            'valid'              => true,
            'code'               => $coupon->code,
            'type'               => $coupon->type,
            'value'              => (float) $coupon->value,
            'original_total'     => round($total, 2),
            'applicable_total'   => round($applicableTotal, 2),
            'discounted_total'   => round($total - $discount, 2),
            'discount_amount'    => round($discount, 2),
            'applicable_items'   => $applicableItems->count(),
            'total_items'        => $cartItems->count(),
        ]);
    }
}