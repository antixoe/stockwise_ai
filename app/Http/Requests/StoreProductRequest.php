<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        return ['category_id' => ['required', 'exists:categories,id'], 'sku' => ['required', 'string', 'max:255', 'unique:products,sku'], 'name' => ['required', 'string', 'max:255'], 'unit' => ['required', 'string', 'max:32'], 'unit_price' => ['required', 'numeric', 'min:0'], 'reorder_point' => ['required', 'numeric', 'min:0'], 'safety_stock' => ['required', 'numeric', 'min:0'], 'minimum_order_quantity' => ['required', 'numeric', 'gt:0'], 'order_multiple' => ['required', 'numeric', 'gt:0'], 'is_active' => ['sometimes', 'boolean']];
    }
}
