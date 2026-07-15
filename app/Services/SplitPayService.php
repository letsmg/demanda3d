<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

/**
 * Serviço de cálculo teórico de divisão de pagamentos (Split Pay).
 *
 * Simula a distribuição de valores entre Vendedor, Transportadora e Plataforma
 * conforme as regras do Stripe Connect:
 *   - Comissão da plataforma
 *   - Valor líquido do produto para o Vendedor
 *   - Valor do frete para a Transportadora
 *
 * Os valores ficam retidos até que a transportadora atualize o status de entrega.
 */
class SplitPayService
{
    /** Comissão da plataforma (percentual sobre o valor do produto) */
    public const PLATFORM_FEE_PERCENT = 10.0;

    /**
     * Calcula a divisão teórica de um pedido.
     *
     * @return array{seller: float, carrier: float, platform: float, total: float}
     */
    public function calculateSplit(Order $order): array
    {
        $itemsTotal = (float) $order->items->sum(fn (OrderItem $item) => $item->subtotal());
        $amountTotal = (float) $order->amount_total;

        // Frete = total do pedido - soma dos itens (se não houver item separado)
        $freight = max(0, $amountTotal - $itemsTotal);

        // Comissão da plataforma sobre o valor dos produtos
        $platformFee = round($itemsTotal * (self::PLATFORM_FEE_PERCENT / 100), 2);

        // Valor líquido do vendedor (produto - comissão)
        $sellerAmount = round($itemsTotal - $platformFee, 2);

        // Valor da transportadora (frete integral)
        $carrierAmount = $freight;

        return [
            'seller'   => $sellerAmount,
            'carrier'  => $carrierAmount,
            'platform' => $platformFee,
            'total'    => round($sellerAmount + $carrierAmount + $platformFee, 2),
        ];
    }

    /**
     * Calcula a divisão para um pedido com item específico de frete já separado.
     */
    public function calculateSplitWithFreightItem(Order $order, float $freightAmount): array
    {
        $itemsTotal = (float) $order->items->sum(fn (OrderItem $item) => $item->subtotal());

        $platformFee = round($itemsTotal * (self::PLATFORM_FEE_PERCENT / 100), 2);
        $sellerAmount = round($itemsTotal - $platformFee, 2);

        return [
            'seller'   => $sellerAmount,
            'carrier'  => $freightAmount,
            'platform' => $platformFee,
            'total'    => round($sellerAmount + $freightAmount + $platformFee, 2),
        ];
    }
}