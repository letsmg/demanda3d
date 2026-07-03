<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
        $user = $request->user();
        $clientUser = \Illuminate\Support\Facades\Auth::guard('clients')->user();

        $cartCount = 0;
        if ($clientUser) {
            $cartCount = \App\Models\CartItem::where('client_id', $clientUser->id)->sum('quantity');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'name' => $user->display_name ?? $user->getDisplayName(),
                ]) : null,
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