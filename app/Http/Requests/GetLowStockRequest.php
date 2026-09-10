<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetLowStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'threshold' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
