<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DatatableLoadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_field' => ['required'],
            'order_type' => ['required', 'in:asc,desc'],
            'current_page' => ['required'],
            'per_page' => ['required'],
            'model' => ['required'],
            'search' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
