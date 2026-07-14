<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrasilApiService
{
    /**
     * Consulta um CNPJ na BrasilAPI.
     *
     * @return array{situation: string, company_name: string, trade_name: string}|null
     */
    public function lookupCnpj(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if (! $response->successful()) {
                Log::warning('BrasilAPI: CNPJ não encontrado ou erro na consulta.', [
                    'cnpj'      => $cnpj,
                    'status'    => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();

            return [
                'situation'    => $data['situacao_cadastral'] ?? 'INATIVA',
                'company_name' => $data['razao_social'] ?? '',
                'trade_name'   => $data['nome_fantasia'] ?? '',
            ];
        } catch (\Exception $e) {
            Log::error('BrasilAPI: falha na requisição.', [
                'cnpj'   => $cnpj,
                'error'  => $e->getMessage(),
            ]);

            return null;
        }
    }
}