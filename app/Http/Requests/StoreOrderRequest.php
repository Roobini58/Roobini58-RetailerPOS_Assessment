<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer' => ['required', 'array'],
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer.required' => 'Customer details are required.',
            'customer.name.required' => 'Customer name is required.',
            'customer.email.required' => 'Customer email is required.',
            'customer.email.email' => 'Please provide a valid email address.',
            'items.required' => 'At least one product item is required for the order.',
            'items.min' => 'At least one product item must be added to the order.',
            'items.*.product_id.required' => 'Product selection is required.',
            'items.*.product_id.exists' => 'Selected product does not exist in inventory.',
            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
        ];
    }
}
