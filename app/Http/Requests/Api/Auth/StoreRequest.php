<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'unique:users'],
            'email' => ['required', 'unique:users'],
            'password' => ['required', 'confirmed'],
            'user_type' => ['required', 'in:job-seeker,employer']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
