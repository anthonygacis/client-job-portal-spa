<?php

namespace App\Http\Requests\Api\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
