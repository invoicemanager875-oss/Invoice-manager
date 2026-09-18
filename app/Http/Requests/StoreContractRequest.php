<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Contract::class);
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'judul_kontrak' => ['required', 'string', 'max:255'],
            'tanggal_kontrak' => ['required', 'date'],
            'pihak_pertama_nama' => ['required', 'string', 'max:255'],
            'pihak_pertama_jabatan' => ['required', 'string', 'max:255'],
            'pihak_pertama_perusahaan' => ['required', 'string', 'max:255'],
            'pihak_pertama_alamat' => ['required', 'string'],
            'pihak_pertama_telepon' => ['nullable', 'string', 'max:50'],
            'pihak_kedua_nama' => ['required', 'string', 'max:255'],
            'pihak_kedua_identitas' => ['nullable', 'string', 'max:50'],
            'pihak_kedua_perusahaan' => ['nullable', 'string', 'max:255'],
            'pihak_kedua_alamat' => ['required', 'string'],
            'pihak_kedua_telepon' => ['required', 'string', 'max:50'],
            'pihak_kedua_email' => ['nullable', 'email', 'max:255'],
            'nilai_kontrak' => ['required', 'numeric', 'min:0'],
            'nilai_terbilang' => ['nullable', 'string', 'max:255'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:365'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date'],
            'narasi' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
            'scopes' => ['nullable', 'array'],
            'scopes.*.nama_paket' => ['required_with:scopes', 'string', 'max:255'],
            'scopes.*.deskripsi' => ['nullable', 'string'],
            'scopes.*.nominal' => ['nullable', 'numeric', 'min:0'],
            'terms' => ['nullable', 'array'],
            'terms.*.judul' => ['required_with:terms', 'string', 'max:255'],
            'terms.*.persentase' => ['required_with:terms', 'numeric', 'min:0', 'max:100'],
            'terms.*.nominal' => ['required_with:terms', 'numeric', 'min:0'],
            'terms.*.syarat_pencairan' => ['nullable', 'string'],
        ];
    }
}
