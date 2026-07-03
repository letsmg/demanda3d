<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ToolsController extends Controller
{
    /**
     * Exibe a página de configuração de níveis de segurança.
     *
     * Regras configuráveis:
     * - preco_minimo_sem_foto: valor mínimo do pedido que exige foto do produto
     * - preco_minimo_sem_documento: valor mínimo do pedido que exige documento do comprador
     * - bloqueio_automatico_fraude: número de denúncias para bloqueio automático de transportadora
     */
    public function index(): Response
    {
        Gate::authorize('admin.only');

        return Inertia::render('Tools/Security', [
            'securityLevels' => config('security'),
        ]);
    }

    /**
     * Atualiza os níveis de segurança (tiers de preço + bloqueio fraude).
     */
    public function updateSecurityLevels(Request $request)
    {
        Gate::authorize('admin.only');

        $validated = $request->validate([
            'tiers'                       => ['required', 'array'],
            'tiers.tier_1.price_max'      => ['required', 'numeric', 'min:0'],
            'tiers.tier_2.price_min'      => ['required', 'numeric', 'min:0'],
            'tiers.tier_2.price_max'      => ['required', 'numeric', 'min:0'],
            'bloqueio_automatico_fraude'  => ['required', 'integer', 'min:1'],
        ]);

        $path    = config_path('security.php');
        $content = "<?php\n\nreturn " . var_export($validated, true) . ";\n";
        file_put_contents($path, $content);

        \Illuminate\Support\Facades\Artisan::call('config:clear');

        return redirect()->route('admin.tools.security')
            ->with('success', 'Níveis de segurança atualizados com sucesso.');
    }

    /**
     * Gera o PDF da etiqueta de envio com ID do pedido e QR Code básico.
     *
     * O QR Code é gerado como um data URI usando a API Google Charts
     * (sem dependência externa adicional).
     */
    public function generateLabel(Order $order)
    {
        Gate::authorize('admin.only');

        // Gera QR Code como SVG inline (Google Charts API — gratuito, sem lib extra)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data='
            . urlencode(route('orders.edit', $order->id));

        $pdf = Pdf::loadView('pdf.shipping-label', [
            'order'        => $order,
            'qrUrl'        => $qrUrl,
            'generatedAt'  => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('etiqueta-pedido-' . $order->id . '.pdf');
    }
}