<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'website' => ['nullable', 'url'],
            'logo' => ['nullable', 'image'],
            'qris' => ['nullable', 'image'],
            'ttd' => ['nullable', 'image'],
            'stempel' => ['nullable', 'image'],
            'materai' => ['nullable', 'image'],
            'ttd_nama' => ['nullable', 'string', 'max:100'],
            'ttd_jabatan' => ['nullable', 'string', 'max:100'],
            'color_header' => ['nullable', 'string'],
            'color_accent' => ['nullable', 'string'],
            'default_desain_tema' => ['nullable', Rule::in([...array_keys(config('invoice_themes')), 'kop-gambar', 'brand'])],
            'canva_link' => ['nullable', 'url'],
            'rekening' => ['nullable', 'array'],
            'rekening.*.bank' => ['nullable', 'string', 'max:100'],
            'rekening.*.norek' => ['nullable', 'string', 'max:50'],
            'rekening.*.nama' => ['nullable', 'string', 'max:100'],
        ];
    }
}
