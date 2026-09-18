@php
    $formOrder = $formOrder ?? null;
    $invoices = $invoices ?? collect();

    $jenisPekerjaanOptions = ['Rumah Tinggal', 'Rumah Kos', 'Ruko', 'Villa', 'Gedung Komersial', 'Renovasi', 'Desain Interior', 'Landscaping'];
    $arahMataAnginOptions = ['Utara', 'Timur Laut', 'Timur', 'Tenggara', 'Selatan', 'Barat Daya', 'Barat', 'Barat Laut', 'Lainnya'];

    $currentJenis = old('jenis_pekerjaan', $formOrder->jenis_pekerjaan ?? '');
    $isCustomJenis = $currentJenis !== '' && ! in_array($currentJenis, $jenisPekerjaanOptions, true);
    $initialJenisSelectValue = $isCustomJenis ? '__custom__' : ($currentJenis ?: $jenisPekerjaanOptions[0]);
    $initialJenisCustomValue = $isCustomJenis ? $currentJenis : '';

    $initialLingkup = old('lingkup_pekerjaan', $formOrder->lingkup_pekerjaan ?? []);
    $initialLingkup = collect($initialLingkup)->values()->all();

    $taskAssignByName = ($formOrder?->tasks ?? collect())->pluck('assigned_to', 'name');
    $initialAssignments = old('tugas_assignments');
    if (! is_array($initialAssignments)) {
        $initialAssignments = collect($initialLingkup)
            ->map(fn ($name) => (string) ($taskAssignByName[$name] ?? ''))
            ->values()
            ->all();
    }

    $existingImages = $formOrder?->images ?? collect();
    $existingRevisions = $formOrder?->revisions ?? collect();

    $packages = collect(config('invoice_packages'))->map(fn ($p) => $p['items'])->all();
    $invoiceItemsMap = $invoices->pluck('items', 'id')->all();
    $brandDraftersMap = $brandDraftersMap ?? [];
@endphp

<div
    x-data="{
        jenisSelectValue: @js($initialJenisSelectValue),
        jenisCustomValue: @js($initialJenisCustomValue),
        lingkup: @js($initialLingkup),
        lingkupInput: '',
        packages: @js($packages),
        invoiceItems: @js($invoiceItemsMap),
        invoiceId: @js(old('invoice_id', $formOrder->invoice_id ?? '')),
        assignments: @js($initialAssignments),
        brandDrafters: @js($brandDraftersMap),
        brandId: @js(old('brand_id', $formOrder->brand_id ?? '')),
        addLingkupManual() {
            if (this.lingkupInput.trim()) {
                this.lingkup.push(this.lingkupInput.trim());
                this.assignments.push('');
                this.lingkupInput = '';
            }
        },
        removeLingkup(i) { this.lingkup.splice(i, 1); this.assignments.splice(i, 1); },
        loadPaket(key) {
            if (this.packages[key]) {
                this.packages[key].forEach(item => { this.lingkup.push(item); this.assignments.push(''); });
            }
        },
        loadFromInvoice(id) {
            if (this.invoiceItems[id]) {
                this.invoiceItems[id].forEach(item => { this.lingkup.push(item); this.assignments.push(''); });
            }
            this.invoiceId = id;
        },
        images: [],
        imgUid: 0,
        addImageSlot() {
            this.imgUid++;
            this.images.push({ _uid: 'new' + this.imgUid, caption: '', size: 'sedang' });
        },
        removeImageSlot(uid) {
            this.images = this.images.filter(im => im._uid !== uid);
        },
        revisions: [],
        revUid: 0,
        addRevisionSlot() {
            this.revUid++;
            this.revisions.push({ _uid: 'newrev' + this.revUid, catatan: '' });
        },
        removeRevisionSlot(uid) {
            this.revisions = this.revisions.filter(r => r._uid !== uid);
        },
    }"
    class="space-y-8"
>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="brand_id" value="Brand" />
            @if (auth()->user()->hasRole('admin'))
                <select id="brand_id" name="brand_id" x-model="brandId" required
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Pilih brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id', $formOrder->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="brand_id" x-model="brandId" value="{{ $brands->first()->id ?? '' }}">
                <p class="mt-1 text-sm text-slate-500">{{ $brands->first()->name ?? '-' }}</p>
            @endif
            <x-input-error :messages="$errors->get('brand_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="tanggal_order" value="Tanggal Order" />
            <x-text-input id="tanggal_order" name="tanggal_order" type="date" class="mt-1 block w-full"
                value="{{ old('tanggal_order', isset($formOrder) ? $formOrder->tanggal_order->format('Y-m-d') : now()->format('Y-m-d')) }}" required />
            <x-input-error :messages="$errors->get('tanggal_order')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="deadline" value="Deadline (opsional)" />
            <x-text-input id="deadline" name="deadline" type="date" class="mt-1 block w-full"
                value="{{ old('deadline', optional($formOrder->deadline ?? null)->format('Y-m-d')) }}" />
            <x-input-error :messages="$errors->get('deadline')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="nama_klien" value="Nama Klien" />
            <x-text-input id="nama_klien" name="nama_klien" type="text" class="mt-1 block w-full"
                value="{{ old('nama_klien', $formOrder->nama_klien ?? '') }}" required autofocus />
            <x-input-error :messages="$errors->get('nama_klien')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="lokasi_project" value="Lokasi Project" />
            <x-text-input id="lokasi_project" name="lokasi_project" type="text" class="mt-1 block w-full"
                value="{{ old('lokasi_project', $formOrder->lokasi_project ?? '') }}" />
            <x-input-error :messages="$errors->get('lokasi_project')" class="mt-1" />
        </div>

        <div>
            <x-input-label value="Jenis Pekerjaan" />
            <select x-model="jenisSelectValue"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach ($jenisPekerjaanOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
                <option value="__custom__">✏️ Tulis Manual...</option>
            </select>
            <input type="text" x-show="jenisSelectValue === '__custom__'" x-model="jenisCustomValue"
                placeholder="Tulis jenis pekerjaan..."
                class="mt-2 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <input type="hidden" name="jenis_pekerjaan"
                :value="jenisSelectValue === '__custom__' ? jenisCustomValue : jenisSelectValue">
            <x-input-error :messages="$errors->get('jenis_pekerjaan')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="ukuran_bangunan" value="Ukuran Bangunan" />
            <x-text-input id="ukuran_bangunan" name="ukuran_bangunan" type="text" class="mt-1 block w-full"
                value="{{ old('ukuran_bangunan', $formOrder->ukuran_bangunan ?? '') }}" placeholder="Contoh: 10x15 m²" />
            <x-input-error :messages="$errors->get('ukuran_bangunan')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label value="Arah Mata Angin" />
            <div class="mt-1 grid grid-cols-3 sm:grid-cols-5 gap-2">
                @foreach ($arahMataAnginOptions as $arah)
                    <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-md px-2 py-1.5 cursor-pointer">
                        <input type="radio" name="arah_mata_angin" value="{{ $arah }}"
                            @checked(old('arah_mata_angin', $formOrder->arah_mata_angin ?? '') === $arah)>
                        {{ $arah }}
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('arah_mata_angin')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="share_location" value="Share Location (link Google Maps)" />
            <x-text-input id="share_location" name="share_location" type="text" class="mt-1 block w-full"
                value="{{ old('share_location', $formOrder->share_location ?? '') }}" placeholder="https://maps.google.com/..." />
            <x-input-error :messages="$errors->get('share_location')" class="mt-1" />
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700">Lingkup Pekerjaan</h3>
            <div class="flex items-center gap-2">
                <select @change="loadPaket($event.target.value); $event.target.value = ''"
                    class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">📦 Muat dari Paket...</option>
                    @foreach (config('invoice_packages') as $key => $paket)
                        <option value="{{ $key }}">{{ $paket['label'] }}</option>
                    @endforeach
                </select>
                @if ($invoices->isNotEmpty())
                    <select @change="loadFromInvoice($event.target.value); $event.target.value = ''"
                        class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">📄 Muat dari Invoice...</option>
                        @foreach ($invoices as $inv)
                            <option value="{{ $inv['id'] }}">{{ $inv['label'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <input type="hidden" name="invoice_id" x-model="invoiceId">
        <div class="mb-3" x-show="invoiceId">
            <span class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full px-3 py-1 text-xs">
                📄 Lingkup disalin dari invoice ini — tetap tertaut
                <button type="button" @click="invoiceId = ''" class="text-emerald-500 hover:text-emerald-700" title="Lepas tautan invoice">&times;</button>
            </span>
        </div>

        <div class="space-y-2 mb-3" x-show="lingkup.length > 0">
            <template x-for="(item, index) in lingkup" :key="index">
                <div class="flex flex-wrap items-center gap-2 bg-sky-50 border border-sky-200 rounded-lg px-3 py-2">
                    <span class="flex-1 min-w-[10rem] text-xs text-sky-700" x-text="item"></span>
                    <input type="hidden" :name="'lingkup_pekerjaan[' + index + ']'" :value="item">

                    @if (config('features.drafter_tasks'))
                        <select :name="'tugas_assignments[' + index + ']'" x-model="assignments[index]"
                            x-show="(brandDrafters[brandId] || []).length > 0"
                            class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">PIC belum ditentukan</option>
                            <template x-for="d in (brandDrafters[brandId] || [])" :key="d.id">
                                <option :value="d.id" x-text="d.name"></option>
                            </template>
                        </select>
                        <span class="text-xs text-slate-400 italic" x-show="(brandDrafters[brandId] || []).length === 0">
                            Belum ada drafter di brand ini
                        </span>
                    @endif

                    <button type="button" @click="removeLingkup(index)" class="text-sky-500 hover:text-sky-700">&times;</button>
                </div>
            </template>
        </div>
        <p class="text-sm text-slate-400 mb-3" x-show="lingkup.length === 0">Belum ada item lingkup pekerjaan.</p>

        <div class="flex gap-2">
            <input type="text" x-model="lingkupInput" @keydown.enter.prevent="addLingkupManual()"
                placeholder="Tambah item lingkup pekerjaan manual..."
                class="flex-1 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <button type="button" @click="addLingkupManual()" class="text-sm bg-navy-600 hover:bg-navy-700 text-white font-semibold px-4 rounded-md">
                + Tambah
            </button>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <x-input-label for="catatan_klien" value="Catatan dari Klien" />
        <textarea id="catatan_klien" name="catatan_klien" rows="3"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan_klien', $formOrder->catatan_klien ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('catatan_klien')" class="mt-1" />
    </div>

    @if ($existingImages->isNotEmpty())
        <div class="border-t border-slate-200 pt-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Lampiran Gambar Tersimpan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($existingImages as $img)
                    <div class="border border-slate-200 rounded-lg p-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($img->path) }}"
                            class="w-full h-56 object-contain rounded border border-slate-200 bg-slate-50 mb-3">
                        <input type="text" name="existing_images[{{ $img->id }}][caption]" value="{{ old('existing_images.'.$img->id.'.caption', $img->caption) }}"
                            placeholder="Keterangan gambar..."
                            class="w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-2">
                        <div class="flex items-center justify-between gap-3">
                            <select name="existing_images[{{ $img->id }}][size]"
                                class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach (\App\Models\FormOrderImage::SIZES as $key => $sizeInfo)
                                    <option value="{{ $key }}" @selected(old('existing_images.'.$img->id.'.size', $img->size ?? 'sedang') === $key)>{{ $sizeInfo['label'] }}</option>
                                @endforeach
                            </select>
                            <label class="flex items-center gap-2 text-xs text-red-600 shrink-0">
                                <input type="checkbox" name="remove_image_ids[]" value="{{ $img->id }}">
                                Hapus gambar ini
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700">Tambah Lampiran Gambar</h3>
            <button type="button" @click="addImageSlot()" class="text-sm text-navy-500 hover:text-navy-700 font-medium">
                + Tambah Gambar
            </button>
        </div>

        <p class="text-sm text-slate-400" x-show="images.length === 0">Belum ada gambar baru ditambahkan.</p>

        <div class="space-y-3">
            <template x-for="(img, index) in images" :key="img._uid">
                <div class="bg-slate-50 rounded-lg p-3 space-y-2">
                    <input type="file" accept="image/*" :name="'images[' + index + '][file]'"
                        class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-navy-50 file:text-navy-600">
                    <input type="text" :name="'images[' + index + '][caption]'" x-model="img.caption"
                        placeholder="Keterangan gambar..."
                        class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <select :name="'images[' + index + '][size]'" x-model="img.size"
                            class="text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach (\App\Models\FormOrderImage::SIZES as $key => $sizeInfo)
                                <option value="{{ $key }}">{{ $sizeInfo['label'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="removeImageSlot(img._uid)" class="text-red-500 hover:text-red-700 text-sm font-medium shrink-0">Hapus</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @if ($existingRevisions->isNotEmpty())
        <div class="border-t border-slate-200 pt-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Riwayat Revisi</h3>
            <div class="space-y-3">
                @foreach ($existingRevisions as $rev)
                    <div class="border border-slate-200 rounded-lg p-3 flex gap-3">
                        @if ($rev->path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($rev->path) }}" class="h-20 w-20 object-cover rounded border border-slate-200">
                        @endif
                        <div class="flex-1">
                            <div class="text-xs text-slate-400 mb-1">{{ $rev->created_at->translatedFormat('d M Y, H:i') }}</div>
                            <p class="text-sm text-slate-700 whitespace-pre-line mb-2">{{ $rev->catatan ?: '-' }}</p>
                            <label class="flex items-center gap-2 text-xs text-red-600">
                                <input type="checkbox" name="remove_revision_ids[]" value="{{ $rev->id }}">
                                Hapus revisi ini
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700">Tambah Revisi</h3>
            <button type="button" @click="addRevisionSlot()" class="text-sm text-navy-500 hover:text-navy-700 font-medium">
                + Tambah Revisi
            </button>
        </div>

        <p class="text-sm text-slate-400" x-show="revisions.length === 0">Belum ada revisi baru ditambahkan.</p>

        <div class="space-y-3">
            <template x-for="(rev, index) in revisions" :key="rev._uid">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start bg-slate-50 rounded-lg p-3">
                    <div class="sm:col-span-6">
                        <textarea :name="'revisions[' + index + '][catatan]'" x-model="rev.catatan" rows="2"
                            placeholder="Catatan revisi..."
                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    </div>
                    <div class="sm:col-span-5">
                        <input type="file" accept="image/*" :name="'revisions[' + index + '][file]'"
                            class="block w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-navy-50 file:text-navy-600">
                    </div>
                    <div class="sm:col-span-1 flex justify-end">
                        <button type="button" @click="removeRevisionSlot(rev._uid)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                    </div>
                </div>
            </template>
        </div>
        <p class="text-xs text-slate-400 mt-2">Tanggal revisi dicatat otomatis sesuai waktu Form Order disimpan.</p>
    </div>

    <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('form-orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
    </div>
</div>
