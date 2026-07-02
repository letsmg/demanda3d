<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFreightContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'carrier_id' => ['required', 'integer', 'exists:carriers,id'],
            'pickup_location' => ['required', 'string', 'max:500'],
            'delivery_location' => ['required', 'string', 'max:500'],
            'cargo_description' => ['required', 'string', 'max:500'],
            'pickup_date' => ['required', 'date'],
            'estimated_delivery_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'freight_value' => ['required', 'numeric', 'min:0'],
            'freight_paid' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:pending,in_transit,delivered,cancelled'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}