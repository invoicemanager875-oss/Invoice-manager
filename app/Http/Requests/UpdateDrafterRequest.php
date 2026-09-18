<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateDrafterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($this->route('drafter'))],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'jobdesk' => ['nullable', Rule::in(User::JOBDESKS)],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
        ];
    }
}
