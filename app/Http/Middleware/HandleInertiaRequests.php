<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        // Staff user (guard: web)
        $staffUser = Auth::guard('web')->user();
        // Carrier user (guard: carriers)
        $carrierUser = Auth::guard('carriers')->user();
        // Cliente autenticado (guard: clients)
        $clientUser = Auth::guard('clients')->user();

        $cartCount = 0;
        if ($clientUser) {
            $cartCount = \App\Models\CartItem::where('client_id', $clientUser->id)->sum('quantity');
        }

        // Detecta qual é o usuário ativo principal
        $activeUser = $staffUser ?? $carrierUser;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $activeUser ? array_merge($activeUser->toArray(), [
                    'name'   => $activeUser->display_name ?? $activeUser->getDisplayName(),
                    'avatar' => $activeUser->avatar ?? null,
                ]) : null,
                'role' => $staffUser ? 'staff' : ($carrierUser ? 'carrier' : null),
            ],
            'auth_client' => [
                'user' => $clientUser ? $clientUser->toArray() : null,
            ],
            'cartCount' => $cartCount,
            'csrf_token' => csrf_token(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}