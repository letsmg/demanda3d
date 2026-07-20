<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendErrorLogController extends Controller
{
    /**
     * POST /api/log-frontend-error
     *
     * Recebe erros não tratados do frontend via navigator.sendBeacon().
     * Registra no canal de log padrão do Laravel para captura pelo Loki/Grafana.
     *
     * Silencioso — nunca retorna erro ao cliente, mesmo se o payload for inválido.
     */
    public function __invoke(Request $request)
    {
        try {
            $message = $request->input('message', 'Erro desconhecido do frontend');
            $stack   = $request->input('stack');
            $url     = $request->input('url', 'unknown');
            $timestamp = $request->input('timestamp');

            Log::error('Erro não tratado no frontend', [
                'message'   => is_string($message) ? $message : json_encode($message),
                'stack'     => is_string($stack) ? $stack : null,
                'url'       => is_string($url) ? $url : 'unknown',
                'timestamp' => is_string($timestamp) ? $timestamp : now()->toIso8601String(),
                'source'    => 'frontend',
            ]);
        } catch (\Throwable) {
            // Silencioso — nunca quebra por payload malformado
        }

        return response()->noContent(202);
    }
}