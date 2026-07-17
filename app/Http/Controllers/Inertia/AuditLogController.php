<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Lista os logs de auditoria com paginação e filtros.
     *
     * Regra de visibilidade:
     * - ADMIN (10, 11): vê todos os logs de todos os tenants.
     * - SELLER (1, 2): vê apenas logs do seu próprio tenant_id.
     * - CARRIER (5, 6): vê logs relacionados à sua transportadora.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $accessLevel = $user->access_level;

        $query = ActivityLog::query()->latestFirst();

        // ── Multi-tenant: Admins veem tudo; sellers veem só seu tenant ──
        if (! $accessLevel->isAdmin()) {
            // Sellers: filtro estrito por tenant_id
            if ($accessLevel->isSeller() && $user->tenant_id) {
                $query->forTenant($user->tenant_id);
            }
        }

        // ── Filtros ──────────────────────────────────────────────
        // Filtro por causer (usuário que executou a ação)
        if ($request->filled('causer_id')) {
            $query->filterByCauser(
                $request->integer('causer_id'),
                $request->get('causer_type')
            );
        }

        // Filtro por evento (tipo da ação)
        if ($request->filled('event')) {
            $query->filterByEvent($request->get('event'));
        }

        // Filtro por subject_type (recurso afetado)
        if ($request->filled('subject_type')) {
            $query->filterBySubjectType($request->get('subject_type'));
        }

        // Filtro por range de data
        if ($request->filled('from') || $request->filled('to')) {
            $query->filterByDateRange(
                $request->get('from'),
                $request->get('to')
            );
        }

        // ── Paginação (10 por página) ────────────────────────────
        $logs = $query->with(['causer', 'subject', 'tenant'])
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only([
                'causer_id', 'causer_type', 'event',
                'subject_type', 'from', 'to',
            ]),
            'isAdmin' => $accessLevel->isAdmin(),
        ]);
    }
}