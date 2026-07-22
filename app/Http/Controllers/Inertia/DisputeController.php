<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\DisputeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisputeController extends Controller
{
    public function __construct(
        private DisputeService $disputeService,
    ) {}

    /**
     * Dashboard de disputas.
     *
     * - Admins: veem todas as disputas de todos os tenants
     * - Sellers: veem apenas disputas do seu tenant (GlobalScope)
     */
    public function index(Request $request): Response
    {
        $disputes = $this->disputeService->listForUser(
            auth()->user(),
            $request->get('per_page', 15),
        );

        return Inertia::render('Disputes/Index', [
            'disputes' => $disputes,
        ]);
    }

    /**
     * Exibe uma disputa específica.
     */
    public function show(Dispute $dispute): Response
    {
        $dispute = $this->disputeService->findForUser($dispute->id, auth()->user());

        return Inertia::render('Disputes/Show', [
            'dispute' => $dispute,
        ]);
    }

    /**
     * Formulário para criar uma nova disputa (admin).
     */
    public function create(Request $request): Response
    {
        $orders = Order::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get(['id', 'client_id', 'tenant_id', 'status', 'amount_total']);

        return Inertia::render('Disputes/Create', [
            'orders' => $orders,
        ]);
    }

    /**
     * Cria uma nova disputa vinculada a um pedido.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'    => ['required', 'integer', 'exists:orders,id'],
            'reason'      => ['required', 'string', 'in:fraud,fake_product,offensive,not_delivered,other'],
            'description' => ['required', 'string', 'min:20'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        try {
            $dispute = $this->disputeService->create(
                tenantId: $order->tenant_id,
                reporterId: $order->client_id,
                orderId: $order->id,
                reason: $validated['reason'],
                description: $validated['description'],
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['description' => $e->getMessage()])->withInput();
        }

        return redirect()->route('disputes.show', $dispute->id)
            ->with('success', 'Disputa criada com sucesso.');
    }

    /**
     * Envia mensagem de admin na disputa.
     * Apenas ADMIN e ADMIN_2 podem enviar mensagens em disputas.
     */
    public function sendMessage(Request $request, Dispute $dispute)
    {
        $this->authorize('sendMessage', $dispute);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1'],
        ]);

        try {
            $this->disputeService->sendMessage(
                dispute: $dispute,
                content: $validated['content'],
                senderId: auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['content' => $e->getMessage()]);
        }

        return back()->with('success', 'Mensagem enviada na disputa.');
    }

    /**
     * Resolve/fecha uma disputa (admin apenas).
     */
    public function resolve(Dispute $dispute)
    {
        $this->authorize('close', $dispute);

        $this->disputeService->resolve($dispute, auth()->id());

        return back()->with('success', 'Disputa resolvida.');
    }

    /**
     * Rejeita/descarta uma disputa (admin apenas).
     */
    public function dismiss(Dispute $dispute)
    {
        $this->authorize('close', $dispute);

        $this->disputeService->dismiss($dispute, auth()->id());

        return back()->with('success', 'Disputa rejeitada.');
    }
}