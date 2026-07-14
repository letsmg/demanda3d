<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreBankDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isStaff();
    }

    public function rules(): array
    {
        $tenant = Tenant::where('user_id', Auth::id())->first();

        return [
            'bank_name'            => ['required', 'string', 'max:100'],
            'routing_number'       => ['required', 'string', 'max:10'],
            'account_number'       => ['required', 'string', 'max:20'],
            'bank_pix_key'         => ['nullable', 'string', 'max:255'],
            'account_holder_name'  => ['required', 'string', 'max:255'],
            'account_holder_doc'   => [
                'required',
                'string',
                'max:18',
                function ($attribute, $value, $fail) use ($tenant) {
                    // Valida que o documento do titular bancário é o mesmo do tenant
                    $doc = preg_replace('/\D/', '', $value);
                    $tenantDoc = preg_replace('/\D/', '', $tenant->document ?? '');

                    if ($doc !== $tenantDoc) {
                        $fail('O CPF/CNPJ do titular da conta bancária deve ser o mesmo do responsável legal cadastrado nesta loja.');
                    }
                },
            ],
            'consented' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'consented.accepted' => 'Você precisa consentir com o processamento dos seus dados financeiros conforme a LGPD.',
        ];
    }
}