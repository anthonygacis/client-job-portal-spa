<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required'],
            'password' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
