<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $tenant = tenant();

        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', $tenant->unique('users', 'email')->ignore($user)],
        ];
    }
}
