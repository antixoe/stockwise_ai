<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return $this->user()?->hasRole('admin', 'warehouse_staff') ?? false; }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['product_id' => ['required', 'exists:products,id'], 'supplier_id' => ['nullable', 'exists:suppliers,id'], 'type' => ['required', 'in:receipt,issue,return_in,return_out,adjustment_in,adjustment_out'], 'quantity' => ['required', 'numeric', 'gt:0'], 'unit_cost' => ['nullable', 'numeric', 'min:0'], 'occurred_at' => ['required', 'date'], 'reference' => ['nullable', 'string', 'max:255'], 'notes' => ['nullable', 'string'], 'metadata' => ['nullable', 'array'], 'idempotency_key' => ['required', 'uuid']];
    }
}
