<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return $this->user()?->hasRole('admin') ?? false; }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $supplier = $this->route('supplier');
        return ['code' => ['required', 'string', 'max:255', 'unique:suppliers,code,'.$supplier?->id], 'name' => ['required', 'string', 'max:255'], 'contact_name' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:64'], 'default_lead_time_days' => ['nullable', 'numeric', 'min:0'], 'is_active' => ['sometimes', 'boolean']];
    }
}
