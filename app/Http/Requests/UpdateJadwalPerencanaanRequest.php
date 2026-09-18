<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalPerencanaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $jadwal = $this->route('jadwal_perencanaan') ?? $this->route('jadwal');

        return $this->user()->can('update', $jadwal);
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'nama_proyek' => ['required', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:365'],
            'tanggal_mulai' => ['required', 'date'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'catatan' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.nama_item' => ['required_with:items', 'string', 'max:255'],
            'items.*.bobot' => ['required_with:items', 'numeric', 'min:0', 'max:100'],
            'items.*.hari_mulai' => ['required_with:items', 'integer', 'min:1'],
            'items.*.hari_selesai' => ['required_with:items', 'integer', 'min:1'],
        ];
    }
}
