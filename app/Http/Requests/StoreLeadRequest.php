<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'brand_id' => ['required', 'exists:brands,id'],

            'klien' => ['required', 'string', 'max:255'],

            'no_wa' => ['nullable', 'string', 'max:32'],

            'tanggal' => ['required', 'date'],

            'jam' => ['nullable', 'date_format:H:i'],

            'kota' => ['nullable', 'string', 'max:255'],

            'paket' => ['nullable', 'string', 'max:255'],

            'status' => ['required', 'in:'.implode(',', array_keys(Lead::STATUSES))],

            'sumber' => ['required', 'in:'.implode(',', array_keys(Lead::SUMBERS))],

        ];
    }
}
