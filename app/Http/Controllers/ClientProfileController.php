<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ClientProfileController extends Controller
{
    /**
     * Ensure the client is authenticated or redirect to login.
     */
    private function guardClient(): \App\Models\Client
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            abort(redirect('/login_cli'));
        }
        return $client;
    }

    public function profile()
    {
        $client = $this->guardClient();
        return Inertia::render('Client/Profile', [
            'client' => $client,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $client = $this->guardClient();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email,' . $client->id],
        ]);

        $client->update([
            'display_name' => trim(strip_tags($validated['display_name'])),
            'email' => trim(strip_tags($validated['email'])),
        ]);

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function orders()
    {
        $client = $this->guardClient();

        $orders = \App\Models\Order::with('product:id,name,slug')
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('Client/Orders', [
            'client' => $client,
            'orders' => $orders,
        ]);
    }

    public function addresses()
    {
        $client = $this->guardClient();
        return Inertia::render('Client/Addresses', [
            'client' => $client,
        ]);
    }

    public function updateAddress(Request $request)
    {
        $client = $this->guardClient();

        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:2'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:255'],
        ]);

        $client->update($validated);

        return back()->with('success', 'Endereço atualizado com sucesso!');
    }

    /**
     * Solicita devolução de um pedido (até 7 dias após entrega).
     */
    public function requestReturn(Request $request, Order $order)
    {
        $client = $this->guardClient();

        if ($order->client_id !== $client->id) {
            abort(403);
        }

        // Verifica se já existe solicitação
        $exists = ReturnRequest::where('order_id', $order->id)->exists();
        if ($exists) {
            return back()->with('error', 'Já existe uma solicitação de devolução para este pedido.');
        }

        // Verifica prazo de 7 dias após entrega
        if (! $order->delivery_date || now()->diffInDays($order->delivery_date) > 7) {
            return back()->with('error', 'O prazo de 7 dias para solicitar devolução expirou.');
        }

        $reasonData = EncryptionService::encryptWithHash(
            $request->input('reason', 'Devolução solicitada pelo cliente.')
        );

        ReturnRequest::create([
            'order_id'          => $order->id,
            'client_id'         => $client->id,
            'status'            => 'requested',
            'reason_encrypted'  => $reasonData['encrypted'],
            'reason_hash'       => $reasonData['hash'],
            'requested_at'      => now(),
        ]);

        return back()->with('success', 'Solicitação de devolução registrada. Você tem até 3 dias para postar o produto.');
    }
}
