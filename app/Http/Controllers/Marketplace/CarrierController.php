<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CarrierController extends Controller
{
    /**
     * Lista todos os transportadores disponíveis no marketplace B2B.
     * Acessível apenas para usuários com tenant (vendedores).
     *
     * Inclui informações sobre acordos já existentes com o tenant atual.
     */
    public function index(Request $request): Response
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            abort(403, 'Apenas vendedores podem acessar o marketplace de transportadoras.');
        }

        $carriers = Carrier::query()
            ->available()
            ->with(['coverageRanges', 'tenantAgreements' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            }])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('fantasy_name', 'ilike', '%' . $request->search . '%');
            })
            ->when($request->filled('cep'), function ($q) use ($request) {
                $q->coversCep($request->cep);
            })
            ->orderBy('fantasy_name')
            ->paginate($request->get('per_page', 12))
            ->withQueryString();

        return Inertia::render('Marketplace/Carriers/Index', [
            'carriers' => $carriers,
            'filters'  => $request->only(['search', 'cep']),
        ]);
    }

    /**
     * Exibe o perfil público de um transportador.
     */
    public function show(Carrier $carrier): Response
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            abort(403, 'Apenas vendedores podem acessar o marketplace de transportadoras.');
        }

        $carrier->load(['coverageRanges']);

        $agreement = CarrierTenantAgreement::where('tenant_id', $tenant->id)
            ->where('carrier_id', $carrier->id)
            ->first();

        return Inertia::render('Marketplace/Carriers/Show', [
            'carrier'   => $carrier,
            'agreement' => $agreement,
        ]);
    }

    /**
     * Envia um convite de conexão do tenant (vendedor) para o carrier.
     * Cria um carrier_tenant_agreement com status 'pending_carrier'.
     */
    public function invite(Carrier $carrier): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            abort(403, 'Apenas vendedores podem enviar convites.');
        }

        // Verifica se já existe um acordo (em qualquer status)
        $existing = CarrierTenantAgreement::where('tenant_id', $tenant->id)
            ->where('carrier_id', $carrier->id)
            ->first();

        if ($existing) {
            return back()->with('error', match ($existing->status) {
                'active'          => 'Você já possui um acordo ativo com esta transportadora.',
                'pending_carrier' => 'Já existe um convite pendente para esta transportadora.',
                'pending_tenant'  => 'Esta transportadora já solicitou conexão — aprove a solicitação.',
                'rejected'        => 'Esta transportadora rejeitou seu convite anterior.',
                default           => 'Já existe um acordo com esta transportadora.',
            });
        }

        DB::transaction(function () use ($tenant, $carrier) {
            CarrierTenantAgreement::create([
                'tenant_id'  => $tenant->id,
                'carrier_id' => $carrier->id,
                'status'     => CarrierTenantAgreement::STATUS_PENDING_CARRIER,
            ]);
        });

        return back()->with('success', "Convite enviado para {$carrier->fantasy_name}.");
    }

    /**
     * Aceita um acordo pendente (tenant aprova solicitação do carrier).
     */
    public function accept(CarrierTenantAgreement $agreement): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant || $agreement->tenant_id !== $tenant->id) {
            abort(403);
        }

        if ($agreement->status !== CarrierTenantAgreement::STATUS_PENDING_TENANT) {
            return back()->with('error', 'Apenas solicitações pendentes do transportador podem ser aceitas.');
        }

        $agreement->activate();

        return back()->with('success', 'Acordo ativado com sucesso.');
    }

    /**
     * Rejeita um acordo pendente.
     */
    public function reject(CarrierTenantAgreement $agreement): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant || $agreement->tenant_id !== $tenant->id) {
            abort(403);
        }

        if (! $agreement->isPending()) {
            return back()->with('error', 'Apenas acordos pendentes podem ser rejeitados.');
        }

        $agreement->reject();

        return back()->with('success', 'Acordo rejeitado.');
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.