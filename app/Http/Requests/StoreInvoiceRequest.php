<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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

            'alamat' => ['nullable', 'string'],

            'phone' => ['nullable', 'string'],

            'email' => ['nullable', 'email'],

            'tanggal' => ['required', 'date'],

            'jatuh_tempo' => ['nullable', 'date'],

            'diskon_persen' => ['nullable', 'numeric', 'between:0,100'],

            'ppn_persen' => ['nullable', 'numeric', 'between:0,100'],

            'catatan' => ['nullable', 'string'],

            'termin_show_pct' => ['nullable', 'boolean'],

            'desain_tema' => ['nullable', 'string', 'in:classic,modern,genz,elegant,pastel,kop-gambar,brand'],

            'kop_image' => ['nullable', 'image', 'max:5120'],

            'qris_image' => ['nullable', 'image', 'max:2048'],

            'sph_aktif' => ['nullable', 'boolean'],

            'sph_perihal' => ['nullable', 'required_if:sph_aktif,1', 'string'],

            'sph_narasi' => ['nullable', 'required_if:sph_aktif,1', 'string'],

            'sph_tempat_tanggal' => ['nullable', 'string'],

            'sph_pengirim' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.deskripsi' => ['required', 'string'],

            'items.*.volume' => ['required', 'numeric', 'min:0'],

            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],

            'items.*.type' => ['required', 'in:single,group,paket'],

            'items.*.satuan' => ['nullable', 'string'],

            'items.*.urutan' => ['nullable', 'integer'],

            'items.*.sub_items' => ['nullable', 'array'],

            'items.*.sub_items.*.deskripsi' => ['required', 'string'],

            'items.*.sub_items.*.volume' => ['required', 'numeric', 'min:0'],

            'items.*.sub_items.*.harga_satuan' => ['required', 'numeric', 'min:0'],

            'items.*.sub_items.*.satuan' => ['nullable', 'string'],

            'terms' => ['nullable', 'array'],

            'terms.*.label' => ['required', 'string'],

            'terms.*.catatan' => ['nullable', 'string'],

            'terms.*.persen' => ['required', 'numeric', 'between:0,100'],

            'terms.*.nominal' => ['nullable', 'numeric', 'min:0'],

            'terms.*.is_lunas' => ['nullable', 'boolean'],

            'terms.*.tanggal_lunas' => ['nullable', 'date'],
        ];
    }
}
