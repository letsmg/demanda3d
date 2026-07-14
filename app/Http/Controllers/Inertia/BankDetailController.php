<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankDetailRequest;
use App\Models\BankDetail;
use App\Models\Tenant;
use App\Services\BrasilApiService;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BankDetailController extends Controller
{
    public function __construct(
        private BrasilApiService $brasilApi,
    ) {}

    /**
     * Exibe o formulário de dados bancários do vendedor/transportadora.
     */
    public function edit(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = $user->tenant;

        if (! $tenant) {
            abort(404, 'Você não possui uma loja cadastrada.');
        }

        $bankDetail = BankDetail::where('tenant_id', $tenant->id)->first();

        return Inertia::render('Settings/BankDetail', [
            'tenant'      => $tenant,
            'bankDetail'  => $bankDetail,
            'document'    => $tenant->document,
            'legal_responsible_name' => $tenant->legal_responsible_name,
        ]);
    }

    /**
     * Salva ou atualiza os dados bancários.
     */
    public function store(StoreBankDetailRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = $user->tenant;

        if (! $tenant) {
            abort(404);
        }

        // Criptografa campos sensíveis
        $encryptionService = app(EncryptionService::class);
        $docData     = $encryptionService->encryptWithHash($request->input('account_holder_doc'));
        $routingData = $encryptionService->encryptWithHash($request->input('routing_number'));
        $accountData = $encryptionService->encryptWithHash($request->input('account_number'));
        $pixData     = $request->input('bank_pix_key')
            ? $encryptionService->encryptWithHash($request->input('bank_pix_key'))
            : ['encrypted' => null, 'hash' => ''];

        // Atualiza o nome do responsável legal no tenant
        $tenant->update([
            'legal_responsible_name' => $request->input('account_holder_name'),
        ]);

        // Upsert dos dados bancários
        BankDetail::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'bank_name'                  => $request->input('bank_name'),
                'routing_number_encrypted'   => $routingData['encrypted'],
                'account_number_encrypted'   => $accountData['encrypted'],
                'bank_pix_key_encrypted'     => $pixData['encrypted'],
                'account_holder_name'        => $request->input('account_holder_name'),
                'account_holder_doc_encrypted' => $docData['encrypted'],
                'account_holder_doc_hash'      => $docData['hash'],
                'consented'                  => true,
                'consented_at'               => now(),
                'consent_ip'                 => $request->ip(),
                'consent_term_version'       => '1.0',
            ]
        );

        return back()->with('success', 'Dados bancários salvos com sucesso.');
    }

    /**
     * API: Consulta CNPJ na BrasilAPI (usado pelo frontend da transportadora).
     */
    public function lookupCnpj(Request $request)
    {
        $request->validate(['cnpj' => ['required', 'string', 'max:18']]);

        $result = $this->brasilApi->lookupCnpj($request->input('cnpj'));

        if (! $result) {
            return response()->json(['error' => 'CNPJ não encontrado ou inativo.'], 404);
        }

        return response()->json($result);
    }
}