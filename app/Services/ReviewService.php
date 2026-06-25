<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    /**
     * Create a new review (compra verificada).
     *
     * @throws ValidationException
     */
    public function create(array $data): Review
    {
        $clientId = auth()->guard('clients')->id()
            ?? throw ValidationException::withMessages(['auth' => 'Cliente não autenticado.']);

        if (! isset($data['order_id'])) {
            throw ValidationException::withMessages(['order_id' => 'O pedido é obrigatório.']);
        }

        $order = Order::findOrFail($data['order_id']);

        // Verifica se o pedido pertence ao cliente autenticado
        if ((int) $order->client_id !== $clientId) {
            throw ValidationException::withMessages(['order_id' => 'Este pedido não pertence a você.']);
        }

        // Verifica se o pedido foi entregue (compra verificada)
        if ($order->status !== 'delivered') {
            throw ValidationException::withMessages(['order_id' => 'Apenas pedidos entregues podem ser avaliados.']);
        }

        // Criptografa o comentário conforme LGPD
        if (isset($data['comment'])) {
            $data['comment_encrypted'] = Crypt::encryptString($data['comment']);
            unset($data['comment']);
        }

        $data['tenant_id'] = $order->tenant_id;
        $data['client_id'] = $clientId;

        return Review::create($data);
    }

    /**
     * Update an existing review.
     */
    public function update(Review $review, array $data): Review
    {
        $clientId = auth()->guard('clients')->id()
            ?? throw ValidationException::withMessages(['auth' => 'Cliente não autenticado.']);

        if ($review->client_id !== $clientId) {
            throw ValidationException::withMessages(['auth' => 'Você não pode editar esta avaliação.']);
        }

        if (isset($data['comment'])) {
            $data['comment_encrypted'] = Crypt::encryptString($data['comment']);
            unset($data['comment']);
        }

        $review->update($data);

        return $review;
    }

    /**
     * Delete a review.
     */
    public function delete(Review $review): bool
    {
        $clientId = auth()->guard('clients')->id()
            ?? throw ValidationException::withMessages(['auth' => 'Cliente não autenticado.']);

        if ($review->client_id !== $clientId) {
            throw ValidationException::withMessages(['auth' => 'Você não pode excluir esta avaliação.']);
        }

        return $review->delete();
    }
}