<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarrierDashboardController extends Controller
{
    /**
     * Dashboard principal da transportadora.
     */
    public function dashboard(Request $request): Response
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->with(['tenantAgreements.tenant', 'coverageRanges'])->first();

        if (! $carrier) {
            abort(404, 'Perfil de transportadora não encontrado.');
        }

        $activeAgreementsCount = $carrier->tenantAgreements()->active()->count();
        $pendingAgreementsCount = $carrier->tenantAgreements()->pending()->count();

        // Orders dos tenants com acordo ativo, mais recentes primeiro
        $activeTenantIds = $carrier->tenantAgreements()->active()->pluck('tenant_id');
        $recentOrders = Order::whereIn('tenant_id', $activeTenantIds)
            ->with(['items', 'client'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Carriers/Dashboard', [
            'carrier'                  => $carrier,
            'activeAgreementsCount'    => $activeAgreementsCount,
            'pendingAgreementsCount'   => $pendingAgreementsCount,
            'recentOrders'             => $recentOrders,
        ]);
    }

    /**
     * Perfil da transportadora — edição dos próprios dados.
     */
    public function profile(): Response
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->with('coverageRanges')->first();

        if (! $carrier) {
            abort(404, 'Perfil de transportadora não encontrado.');
        }

        return Inertia::render('Carriers/Profile', [
            'carrier' => $carrier,
        ]);
    }

    /**
     * Atualiza dados do perfil da transportadora.
     */
    public function updateProfile(Request $request)
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'fantasy_name' => ['required', 'string', 'max:255'],
            'website_url'  => ['nullable', 'url', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'string', 'max:500'],
        ]);

        // Atualiza campos públicos diretamente
        $carrier->update([
            'fantasy_name' => $validated['fantasy_name'],
            'website_url'  => $validated['website_url'] ?? $carrier->website_url,
        ]);

        // Atualiza campos sensíveis com criptografia
        if (isset($validated['phone'])) {
            $phoneData = \App\Services\EncryptionService::encryptWithHash($validated['phone']);
            $carrier->update([
                'phone_encrypted' => $phoneData['encrypted'],
            ]);
        }

        if (isset($validated['address'])) {
            $addressData = \App\Services\EncryptionService::encryptWithHash($validated['address']);
            $carrier->update([
                'address_encrypted' => $addressData['encrypted'],
            ]);
        }

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }

    /**
     * Lista acordos comerciais (contratos) com vendedores.
     */
    public function agreements(): Response
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->firstOrFail();

        $agreements = CarrierTenantAgreement::where('carrier_id', $carrier->id)
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Carriers/Agreements', [
            'agreements' => $agreements,
        ]);
    }

    /**
     * Transportadora aceita um convite pendente (pending_carrier → active).
     */
    public function acceptAgreement(CarrierTenantAgreement $agreement)
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->firstOrFail();

        if ($agreement->carrier_id !== $carrier->id) {
            abort(403);
        }

        if ($agreement->status !== CarrierTenantAgreement::STATUS_PENDING_CARRIER) {
            return back()->with('error', 'Apenas convites pendentes podem ser aceitos.');
        }

        $agreement->activate();

        return back()->with('success', 'Convite aceito! Acordo ativado.');
    }

    /**
     * Transportadora rejeita um convite.
     */
    public function rejectAgreement(CarrierTenantAgreement $agreement)
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->firstOrFail();

        if ($agreement->carrier_id !== $carrier->id) {
            abort(403);
        }

        if (! $agreement->isPending()) {
            return back()->with('error', 'Apenas acordos pendentes podem ser rejeitados.');
        }

        $agreement->reject();

        return back()->with('success', 'Convite rejeitado.');
    }

    /**
     * Orders de todos os tenants com acordo ativo.
     */
    public function orders(Request $request): Response
    {
        $user    = auth()->guard('carriers')->user();
        $carrier = Carrier::where('user_id', $user->id)->firstOrFail();

        $activeTenantIds = $carrier->tenantAgreements()->active()->pluck('tenant_id');

        $orders = Order::whereIn('tenant_id', $activeTenantIds)
            ->with(['items', 'client', 'tenant'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return Inertia::render('Carriers/Orders', [
            'orders' => $orders,
        ]);
    }
}