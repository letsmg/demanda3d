<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CepService;
use Illuminate\Http\JsonResponse;

class CepController extends Controller
{
    public function __construct(
        private CepService $cepService,
    ) {}

    /**
     * GET /api/cep/{cep}
     *
     * Retorna state_id, uf e state_name correspondentes ao CEP.
     */
    public function show(string $cep): JsonResponse
    {
        // Remove caracteres não numéricos
        $cep = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cep) < 8) {
            return response()->json([
                'error' => 'CEP inválido. Informe 8 dígitos.',
            ], 422);
        }

        $data = $this->cepService->lookup($cep);

        return response()->json($data);
    }
}