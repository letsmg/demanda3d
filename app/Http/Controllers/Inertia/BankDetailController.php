<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankDetailCarrierRequest;
use App\Http\Requests\StoreBankDetailRequest;
use App\Mail\BankDetailChangeVerification;
use App\Models\BankDetail;
use App\Models\Tenant;
use App\Services\BrasilApiService;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
            'type'        => 'tenant',
        ]);
    }

    /**
     * Exibe o formulário de dados bancários da transportadora.
     */
    public function editCarrier(): Response
    {
        $user = Auth::guard('carriers')->user();
        if (! $user || ! $user->isCarrier()) {
            abort(403);
        }

        $carrier = \App\Models\Carrier::where('user_id', $user->id)->first();
        if (! $carrier) {
            abort(404, 'Transportadora não encontrada.');
        }

        $bankDetail = BankDetail::where('carrier_id', $carrier->id)->first();

        return Inertia::render('Settings/BankDetail', [
            'tenant'      => null,
            'bankDetail'  => $bankDetail,
            'document'    => $carrier->document ?? '',
            'legal_responsible_name' => $carrier->fantasy_name,
            'type'        => 'carrier',
        ]);
    }

    /**
     * Salva os dados bancários da transportadora com pendência de confirmação por e-mail.
     */
    public function storeCarrier(StoreBankDetailCarrierRequest $request)
    {
        $user = Auth::guard('carriers')->user();
        $carrier = \App\Models\Carrier::where('user_id', $user->id)->first();

        if (! $carrier) {
            abort(404);
        }

        $encryptionService = app(EncryptionService::class);
        $docData     = $encryptionService->encryptWithHash($request->input('account_holder_doc'));
        $routingData = $encryptionService->encryptWithHash($request->input('routing_number'));
        $accountData = $encryptionService->encryptWithHash($request->input('account_number'));
        $pixData     = $request->input('bank_pix_key')
            ? $encryptionService->encryptWithHash($request->input('bank_pix_key'))
            : ['encrypted' => null, 'hash' => ''];

        $pendingData = json_encode([
            'bank_name'                  => $request->input('bank_name'),
            'routing_number_encrypted'   => $routingData['encrypted'],
            'account_number_encrypted'   => $accountData['encrypted'],
            'bank_pix_key_encrypted'     => $pixData['encrypted'],
            'account_holder_name'        => $request->input('account_holder_name'),
            'account_holder_doc_encrypted' => $docData['encrypted'],
            'account_holder_doc_hash'      => $docData['hash'],
            'consented'                  => true,
            'consented_at'               => now()->toIso8601String(),
            'consent_ip'                 => $request->ip(),
            'consent_term_version'       => '1.0',
        ]);

        $token = hash('sha256', Str::random(40) . $carrier->id . now()->timestamp);

        $bankDetail = BankDetail::updateOrCreate(
            ['carrier_id' => $carrier->id],
            [
                'pending_token' => $token,
                'pending_data'  => $pendingData,
                'pending_at'    => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(new BankDetailChangeVerification($bankDetail, $token));

            Log::info('E-mail de confirmação de dados bancários da transportadora enviado.', [
                'carrier_id' => $carrier->id,
                'email'      => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de confirmação bancária da transportadora.', [
                'carrier_id' => $carrier->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Um e-mail de confirmação foi enviado para ' . $user->email . '. Clique no link para confirmar a alteração dos dados bancários.');
    }

    /**
     * Salva os dados bancários do vendedor com pendência de confirmação por e-mail.
     *
     * 1. Salva os dados como "pending" no banco
     * 2. Envia e-mail de confirmação ao vendedor
     * 3. Só aplica os dados ao clicar no link do e-mail
     */
    public function store(StoreBankDetailRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $tenant = $user->tenant;

        if (! $tenant) {
            abort(404);
        }

        $encryptionService = app(EncryptionService::class);
        $docData     = $encryptionService->encryptWithHash($request->input('account_holder_doc'));
        $routingData = $encryptionService->encryptWithHash($request->input('routing_number'));
        $accountData = $encryptionService->encryptWithHash($request->input('account_number'));
        $pixData     = $request->input('bank_pix_key')
            ? $encryptionService->encryptWithHash($request->input('bank_pix_key'))
            : ['encrypted' => null, 'hash' => ''];

        $pendingData = json_encode([
            'bank_name'                  => $request->input('bank_name'),
            'routing_number_encrypted'   => $routingData['encrypted'],
            'account_number_encrypted'   => $accountData['encrypted'],
            'bank_pix_key_encrypted'     => $pixData['encrypted'],
            'account_holder_name'        => $request->input('account_holder_name'),
            'account_holder_doc_encrypted' => $docData['encrypted'],
            'account_holder_doc_hash'      => $docData['hash'],
            'consented'                  => true,
            'consented_at'               => now()->toIso8601String(),
            'consent_ip'                 => $request->ip(),
            'consent_term_version'       => '1.0',
            'legal_responsible_name'     => $request->input('account_holder_name'),
        ]);

        $token = hash('sha256', Str::random(40) . $tenant->id . now()->timestamp);

        // Upsert com dados pendentes
        $bankDetail = BankDetail::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'pending_token' => $token,
                'pending_data'  => $pendingData,
                'pending_at'    => now(),
            ]
        );

        // Envia e-mail de confirmação
        try {
            Mail::to($user->email)->send(new BankDetailChangeVerification($bankDetail, $token));

            Log::info('E-mail de confirmação de dados bancários enviado.', [
                'tenant_id' => $tenant->id,
                'user_id'   => $user->id,
                'email'     => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Falha ao enviar e-mail de confirmação bancária.', [
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Um e-mail de confirmação foi enviado para ' . $user->email . '. Clique no link para confirmar a alteração dos dados bancários.');
    }

    /**
     * Confirma a alteração de dados bancários via link do e-mail.
     */
    public function verify(Request $request)
    {
        $token = $request->query('token');

        if (! $token || strlen($token) !== 64) {
            abort(400, 'Token inválido.');
        }

        $bankDetail = BankDetail::where('pending_token', $token)->first();

        if (! $bankDetail) {
            abort(404, 'Token não encontrado ou já utilizado.');
        }

        // Verifica expiração (60 minutos)
        if ($bankDetail->pending_at && $bankDetail->pending_at->diffInMinutes(now()) > 60) {
            $bankDetail->update([
                'pending_token' => null,
                'pending_data'  => null,
                'pending_at'    => null,
            ]);

            abort(410, 'O link de confirmação expirou. Solicite uma nova alteração.');
        }

        // Aplica os dados pendentes
        $data = json_decode($bankDetail->pending_data, true);

        if (! $data) {
            abort(400, 'Dados pendentes inválidos.');
        }

        $bankDetail->update(array_merge($data, [
            'pending_token' => null,
            'pending_data'  => null,
            'pending_at'    => null,
            'consented_at'  => $data['consented_at'] ?? now(),
        ]));

        // Atualiza o nome do responsável legal no tenant
        if (! empty($data['legal_responsible_name'])) {
            $bankDetail->tenant->update([
                'legal_responsible_name' => $data['legal_responsible_name'],
            ]);
        }

        Log::info('Dados bancários confirmados via e-mail.', [
            'tenant_id' => $bankDetail->tenant_id,
            'token'     => $token,
        ]);

        return redirect('/dashboard')->with('success', 'Dados bancários confirmados com sucesso!');
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