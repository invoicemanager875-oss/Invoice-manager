@php
    $brand = $brand ?? null;

    $initialRekening = old('rekening', $brand->rekening_config ?? []);
    $initialRekening = collect($initialRekening)->values()->all();
@endphp

<div
    x-data="{
        rekening: @js($initialRekening).map((r, i) => ({ ...r, _uid: 'r' + i })),
        uid: @js(count($initialRekening)),
        addRekening() {
            this.uid++;
            this.rekening.push({ bank: '', norek: '', nama: '', _uid: 'r' + this.uid });
        },
        removeRekening(uid) {
            this.rekening = this.rekening.filter(r => r._uid !== uid);
        },
    }"
    class="space-y-8"
>

    @if ($brand?->code)
        <div>
            <x-input-label value="Kode Brand" />
            <p class="mt-1 text-sm font-mono text-slate-500">{{ $brand->code }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Nama Brand" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                value="{{ old('name', $brand->name ?? '') }}" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="npwp" value="NPWP" />
            <x-text-input id="npwp" name="npwp" type="text" class="mt-1 block w-full"
                value="{{ old('npwp', $brand->npwp ?? '') }}" />
            <x-input-error :messages="$errors->get('npwp')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="Telepon" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                value="{{ old('phone', $brand->phone ?? '') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                value="{{ old('email', $brand->email ?? '') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="website" value="Website" />
            <x-text-input id="website" name="website" type="text" class="mt-1 block w-full"
                value="{{ old('website', $brand->website ?? '') }}" />
            <x-input-error :messages="$errors->get('website')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="address" value="Alamat" />
            <textarea id="address" name="address" rows="3"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $brand->address ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-1" />
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Tanda Tangan &amp; Stempel</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="ttd_nama" value="Nama Penandatangan" />
                <x-text-input id="ttd_nama" name="ttd_nama" type="text" class="mt-1 block w-full"
                    value="{{ old('ttd_nama', $brand->ttd_nama ?? '') }}" />
                <x-input-error :messages="$errors->get('ttd_nama')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="ttd_jabatan" value="Jabatan Penandatangan" />
                <x-text-input id="ttd_jabatan" name="ttd_jabatan" type="text" class="mt-1 block w-full"
                    value="{{ old('ttd_jabatan', $brand->ttd_jabatan ?? '') }}" />
                <x-input-error :messages="$errors->get('ttd_jabatan')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Berkas</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach (['logo' => 'Logo', 'qris' => 'QRIS', 'ttd' => 'Tanda Tangan', 'stempel' => 'Stempel', 'materai' => 'Materai'] as $field => $label)
                <div x-data="{ removeFile: false }">
                    <x-input-label for="{{ $field }}" value="{{ $label }}" />

                    @php $path = $brand?->{$field.'_path'}; @endphp
                    @if ($path)
                        <div class="mt-2 mb-2">
                            <div class="relative inline-block" x-show="!removeFile">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($path) }}" alt="{{ $label }}"
                                    class="h-16 rounded border border-slate-200 bg-white object-contain">
                                <button type="button" @click="removeFile = true" title="Hapus {{ $label }}"
                                    class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-red-600 hover:bg-red-700 text-white border-2 border-white flex items-center justify-center text-xs font-bold leading-none">
                                    &times;
                                </button>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-slate-400" x-show="removeFile" x-cloak>
                                <span>{{ $label }} akan dihapus saat disimpan.</span>
                                <button type="button" @click="removeFile = false" class="text-navy-600 hover:underline">Batalkan</button>
                            </div>
                        </div>
                        <input type="hidden" name="remove_{{ $field }}" :value="removeFile ? 1 : 0">
                    @endif

                    <input id="{{ $field }}" name="{{ $field }}" type="file" accept="image/*"
                        class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-navy-50 file:text-navy-600 hover:file:bg-navy-100">
                    <x-input-error :messages="$errors->get($field)" class="mt-1" />
                </div>
            @endforeach
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700">Rekening Pembayaran</h3>
            <button type="button" @click="addRekening()" class="text-sm text-navy-500 hover:text-navy-700 font-medium">
                + Tambah Rekening
            </button>
        </div>

        <div class="space-y-3" x-show="rekening.length === 0">
            <p class="text-sm text-slate-400">Belum ada rekening ditambahkan.</p>
        </div>

        <div class="space-y-3">
            <template x-for="(r, index) in rekening" :key="r._uid">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start bg-slate-50 rounded-lg p-3">
                    <div class="sm:col-span-4">
                        <input type="text" :name="'rekening[' + index + '][bank]'" x-model="r.bank"
                            placeholder="Nama Bank"
                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <input type="text" :name="'rekening[' + index + '][norek]'" x-model="r.norek"
                            placeholder="No. Rekening"
                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <div class="sm:col-span-4">
                        <input type="text" :name="'rekening[' + index + '][nama]'" x-model="r.nama"
                            placeholder="Atas Nama"
                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <div class="sm:col-span-1 flex justify-end">
                        <button type="button" @click="removeRekening(r._uid)" class="text-red-500 hover:text-red-700 text-sm">
                            &times;
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">Tampilan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <x-input-label for="color_header" value="Warna Header" />
                <input id="color_header" name="color_header" type="color" class="mt-1 block h-10 w-20 rounded border border-gray-300"
                    value="{{ old('color_header', $brand->color_header ?? '#1a365d') }}">
                <x-input-error :messages="$errors->get('color_header')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="color_accent" value="Warna Aksen" />
                <input id="color_accent" name="color_accent" type="color" class="mt-1 block h-10 w-20 rounded border border-gray-300"
                    value="{{ old('color_accent', $brand->color_accent ?? '#c9a227') }}">
                <x-input-error :messages="$errors->get('color_accent')" class="mt-1" />
            </div>

            <div class="sm:col-span-1">
                <x-input-label for="canva_link" value="Link Canva" />
                <x-text-input id="canva_link" name="canva_link" type="text" class="mt-1 block w-full"
                    value="{{ old('canva_link', $brand->canva_link ?? '') }}" />
                <x-input-error :messages="$errors->get('canva_link')" class="mt-1" />
            </div>

            <div class="sm:col-span-3">
                <x-input-label for="default_desain_tema" value="Model Desain Standar Invoice" />
                <p class="mt-1 mb-2 text-xs text-slate-400">Otomatis terpilih setiap membuat invoice baru untuk brand ini, supaya tidak perlu pilih ulang tiap kali.</p>
                <select id="default_desain_tema" name="default_desain_tema"
                    class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="brand" @selected(old('default_desain_tema', $brand->default_desain_tema ?? '') === 'brand')>Sesuai Warna Brand (Header/Aksen di atas)</option>
                    @foreach (config('invoice_themes') as $key => $theme)
                        <option value="{{ $key }}" @selected(old('default_desain_tema', $brand->default_desain_tema ?? 'classic') === $key)>{{ $theme['label'] }}</option>
                    @endforeach
                    <option value="kop-gambar" @selected(old('default_desain_tema', $brand->default_desain_tema ?? '') === 'kop-gambar')>Upload Gambar Kop Surat</option>
                </select>
                <x-input-error :messages="$errors->get('default_desain_tema')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('brands.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
    </div>
</div>
