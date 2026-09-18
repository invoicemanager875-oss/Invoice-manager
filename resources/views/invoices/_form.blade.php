@php
    $invoice = $invoice ?? null;

    $submittedItems = old('items');

    $initialItems = $submittedItems
        ? collect($submittedItems)->map(fn ($item) => [
            'id' => $item['id'] ?? null,
            'type' => $item['type'] ?? 'single',
            'deskripsi' => $item['deskripsi'] ?? '',
            'volume' => (string) ($item['volume'] ?? '1'),
            'satuan' => $item['satuan'] ?? '',
            'harga_satuan' => (string) ($item['harga_satuan'] ?? '0'),
            'sub_items' => collect($item['sub_items'] ?? [])->map(fn ($sub) => [
                'id' => $sub['id'] ?? null,
                'deskripsi' => $sub['deskripsi'] ?? '',
                'volume' => (string) ($sub['volume'] ?? '1'),
                'satuan' => $sub['satuan'] ?? '',
                'harga_satuan' => (string) ($sub['harga_satuan'] ?? '0'),
            ])->values()->all(),
        ])->values()->all()
        : ($invoice
            ? $invoice->items->whereNull('parent_item_id')->map(fn ($item) => [
                'id' => $item->id,
                'type' => $item->type,
                'deskripsi' => $item->deskripsi,
                'volume' => (string) $item->volume,
                'satuan' => $item->satuan,
                'harga_satuan' => (string) $item->harga_satuan,
                'sub_items' => $item->subItems->map(fn ($sub) => [
                    'id' => $sub->id,
                    'deskripsi' => $sub->deskripsi,
                    'volume' => (string) $sub->volume,
                    'satuan' => $sub->satuan,
                    'harga_satuan' => (string) $sub->harga_satuan,
                ])->values()->all(),
            ])->values()->all()
            : [['id' => null, 'type' => 'single', 'deskripsi' => '', 'volume' => '1', 'satuan' => '', 'harga_satuan' => '0', 'sub_items' => []]]);

    $initialTerms = $invoice
        ? $invoice->terms->map(fn ($term) => [
            'id' => $term->id,
            'label' => $term->label,
            'catatan' => $term->catatan ?? '',
            'persen' => (string) $term->persen,
            'nominal' => (string) $term->nominal,
            'is_lunas' => (bool) $term->is_lunas,
            'tanggal_lunas' => optional($term->tanggal_lunas)->format('Y-m-d') ?? '',
        ])->values()->all()
        : [];

    $sph = $invoice->sph_config ?? [];
@endphp

<div
    x-data="{
        items: @js($initialItems).map((item, i) => ({
            ...item,
            sub_items: (item.sub_items || []).map((sub, si) => ({ ...sub, _uid: 'i' + i + 's' + si })),
            _uid: 'i' + i,
        })),
        terms: @js($initialTerms).map((term, i) => ({ ...term, _uid: 't' + i })),
        packages: @js(config('invoice_packages')),
        uid: @js(count($initialItems) + count($initialTerms)) + 100,
        diskon: {{ (float) old('diskon_persen', $invoice->diskon_persen ?? 0) }},
        ppn: {{ (float) old('ppn_persen', $invoice->ppn_persen ?? 0) }},
        klien: @js(old('klien', $invoice->klien ?? '')),
        brandName: @js($invoice->brand->name ?? ''),
        brandThemes: @js($brands->pluck('default_desain_tema', 'id')),
        sphAktif: {{ old('sph_aktif', ($sph['aktif'] ?? false) ? '1' : '0') === '1' ? 'true' : 'false' }},
        desainTema: @js(old('desain_tema', $invoice->desain_tema ?? 'classic')),
        terminShowPct: {{ old('termin_show_pct', ($invoice->termin_show_pct ?? true) ? '1' : '0') === '1' ? 'true' : 'false' }},
        terminAktif: {{ count($initialTerms) > 0 ? 'true' : 'false' }},

        addItem() {
            this.uid++;
            this.items.push({ id: null, type: 'single', deskripsi: '', volume: '1', satuan: '', harga_satuan: '0', sub_items: [], _uid: 'i' + this.uid });
        },
        addGroup() {
            this.uid++;
            this.items.push({ id: null, type: 'group', deskripsi: '', volume: '1', satuan: 'ls', harga_satuan: '0', sub_items: [], _uid: 'i' + this.uid });
            this.addSubItem(this.items[this.items.length - 1]);
        },
        addSubItem(item) {
            this.uid++;
            item.sub_items.push({ id: null, deskripsi: '', volume: '1', satuan: 'unit', harga_satuan: '0', _uid: 's' + this.uid });
        },
        removeSubItem(item, uid) {
            item.sub_items = item.sub_items.filter(s => s._uid !== uid);
        },
        loadPaket(key) {
            if (!key || !this.packages[key]) return;
            this.uid++;
            const paket = this.packages[key];
            const lines = paket.label + '\n' + paket.items.map(l => '• ' + l).join('\n');
            this.items.push({ id: null, type: 'paket', deskripsi: lines, volume: '1', satuan: 'paket', harga_satuan: '0', sub_items: [], _uid: 'i' + this.uid });
        },
        removeItem(uid) {
            if (this.items.length > 1) {
                this.items = this.items.filter(i => i._uid !== uid);
            }
        },
        addTerm() {
            this.uid++;
            const labels = ['I', 'II', 'III', 'IV', 'V', 'VI'];
            const idx = this.terms.length;
            this.terms.push({ id: null, label: 'Termin ' + (labels[idx] || (idx + 1)), catatan: '', persen: '0', nominal: '0', is_lunas: false, tanggal_lunas: '', _uid: 't' + this.uid });
        },
        removeTerm(uid) {
            this.terms = this.terms.filter(t => t._uid !== uid);
        },
        toggleTerminAktif() {
            if (this.terminAktif && this.terms.length === 0) {
                const labels = ['I', 'II', 'III'];
                const pcts = [30, 40, 30];
                const total = this.total();
                this.terms = pcts.map((pct, i) => {
                    this.uid++;
                    return { id: null, label: 'Termin ' + labels[i], catatan: '', persen: String(pct), nominal: String(Math.round(total * pct / 100)), is_lunas: false, tanggal_lunas: '', _uid: 't' + this.uid };
                });
            }
        },
        recalcTerms() {
            const total = this.total();
            this.terms.forEach(t => {
                const pct = parseFloat(t.persen) || 0;
                if (pct > 0) t.nominal = String(Math.round(total * pct / 100));
            });
        },
        loadTerminPreset(val) {
            if (!val) return;
            const labels = ['I', 'II', 'III', 'IV', 'V', 'VI'];
            if (val === 'custom') {
                this.terms = [0, 1].map(i => {
                    this.uid++;
                    return { id: null, label: 'Termin ' + labels[i], catatan: '', persen: '0', nominal: '0', is_lunas: false, tanggal_lunas: '', _uid: 't' + this.uid };
                });
                return;
            }
            const parts = val.split('-').slice(1).map(Number);
            const total = this.total();
            this.terms = parts.map((pct, i) => {
                this.uid++;
                return {
                    id: null,
                    label: 'Termin ' + (labels[i] || (i + 1)),
                    catatan: '',
                    persen: String(pct),
                    nominal: String(Math.round(total * pct / 100)),
                    is_lunas: false,
                    tanggal_lunas: '',
                    _uid: 't' + this.uid,
                };
            });
        },
        itemTotal(item) {
            if (item.type === 'group') {
                return item.sub_items.reduce((s, c) => s + ((parseFloat(c.volume) || 0) * (parseFloat(c.harga_satuan) || 0)), 0);
            }
            return (parseFloat(item.volume) || 0) * (parseFloat(item.harga_satuan) || 0);
        },
        subtotal() {
            return this.items.reduce((sum, item) => sum + this.itemTotal(item), 0);
        },
        total() {
            const s = this.subtotal();
            return s * (1 - (parseFloat(this.diskon) || 0) / 100) * (1 + (parseFloat(this.ppn) || 0) / 100);
        },
        terbilang(n) {
            n = Math.floor(Math.abs(n));
            if (n === 0) return 'Nol';

            const satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
            const convert = (num) => {
                if (num < 12) return satuan[num];
                if (num < 20) return convert(num - 10) + ' Belas';
                if (num < 100) return convert(Math.floor(num / 10)) + ' Puluh' + (num % 10 !== 0 ? ' ' + convert(num % 10) : '');
                if (num < 200) return 'Seratus' + (num % 100 !== 0 ? ' ' + convert(num % 100) : '');
                if (num < 1000) return convert(Math.floor(num / 100)) + ' Ratus' + (num % 100 !== 0 ? ' ' + convert(num % 100) : '');
                if (num < 2000) return 'Seribu' + (num % 1000 !== 0 ? ' ' + convert(num % 1000) : '');
                if (num < 1000000) return convert(Math.floor(num / 1000)) + ' Ribu' + (num % 1000 !== 0 ? ' ' + convert(num % 1000) : '');
                if (num < 1000000000) return convert(Math.floor(num / 1000000)) + ' Juta' + (num % 1000000 !== 0 ? ' ' + convert(num % 1000000) : '');
                if (num < 1000000000000) return convert(Math.floor(num / 1000000000)) + ' Miliar' + (num % 1000000000 !== 0 ? ' ' + convert(num % 1000000000) : '');
                return convert(Math.floor(num / 1000000000000)) + ' Triliun' + (num % 1000000000000 !== 0 ? ' ' + convert(num % 1000000000000) : '');
            };

            return convert(n).trim();
        },
        generateSphNarasi() {
            const klien = this.klien || '[Nama Klien]';
            const brand = this.brandName || 'kami';
            let counter = 0;
            const itemLines = this.items.length
                ? this.items.map(item => {
                    counter++;
                    if (item.type === 'group') {
                        const subLines = item.sub_items
                            .filter(s => s.deskripsi)
                            .map(s => '      • ' + s.deskripsi)
                            .join('\n');
                        return '   ' + counter + '. ' + (item.deskripsi || '(Tanpa nama)') + (subLines ? '\n' + subLines : '');
                    }
                    if (item.type === 'paket') {
                        const lines = item.deskripsi.split('\n').filter(l => l.trim() !== '');
                        const title = lines[0] || '(Tanpa nama)';
                        const rest = lines.slice(1).map(l => '      ' + l).join('\n');
                        return '   ' + counter + '. ' + title + (rest ? '\n' + rest : '');
                    }
                    return '   ' + counter + '. ' + item.deskripsi;
                }).join('\n')
                : '   1. [Deskripsi Pekerjaan]';
            const total = this.total();
            const totalRounded = Math.round(total);
            const totalStr = total > 0
                ? ('Rp ' + totalRounded.toLocaleString('id-ID') + ' (' + this.terbilang(totalRounded) + ' Rupiah)')
                : '[Total Biaya]';

            this.sphPerihal = 'Penawaran Jasa Desain ' + klien;
            this.sphNarasi = 'Kepada Yth.\nBapak/Ibu ' + klien + '\ndi Tempat\n\n'
                + 'Dengan hormat,\n\nBersama surat ini kami dari ' + brand + ' bermaksud mengajukan penawaran jasa kepada Bapak/Ibu ' + klien + '.\n\n'
                + 'Adapun ruang lingkup pekerjaan yang kami tawarkan meliputi:\n' + itemLines + '\n\n'
                + 'Dengan total biaya penawaran sebesar:\n   ' + totalStr + '\n\n'
                + 'Demikian surat penawaran ini kami sampaikan. Atas perhatian dan kepercayaan Bapak/Ibu, kami ucapkan terima kasih.';
        },
        sphPerihal: @js(old('sph_perihal', $sph['perihal'] ?? '')),
        sphNarasi: @js(old('sph_narasi', $sph['narasi'] ?? '')),
    }"
    class="space-y-8"
>
    <input type="hidden" name="invoice_id" value="{{ $invoice->id ?? '' }}">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label value="Brand" />
            @if ($invoice)
                <p class="mt-1 text-sm font-medium text-slate-700">{{ $invoice->brand->name }}</p>
                <input type="hidden" name="brand_id" value="{{ $invoice->brand_id }}">
            @else
                <select name="brand_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required @change="brandName = $event.target.selectedOptions[0]?.text || ''; desainTema = brandThemes[$event.target.value] || 'classic'">
                    <option value="">Pilih brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            @endif
            <x-input-error :messages="$errors->get('brand_id')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="klien" value="Nama Klien" />
            <x-text-input id="klien" name="klien" type="text" class="mt-1 block w-full" x-model="klien"
                value="{{ old('klien', $invoice->klien ?? '') }}" required />
            <x-input-error :messages="$errors->get('klien')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="phone" value="Telepon Klien" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full"
                value="{{ old('phone', $invoice->phone ?? '') }}" />
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" value="Email Klien" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                value="{{ old('email', $invoice->email ?? '') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="alamat" value="Alamat Klien" />
            <textarea id="alamat" name="alamat" rows="2"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('alamat', $invoice->alamat ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="tanggal" value="Tanggal Invoice" />
            <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full"
                value="{{ old('tanggal', optional($invoice->tanggal ?? null)->format('Y-m-d')) }}" required />
            <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
        </div>

        <div>
            <x-input-label value="Model Desain" />
            <p class="mt-1 text-sm text-slate-500">
                @if ($invoice)
                    {{ $invoice->desain_tema_label }}
                @else
                    Otomatis mengikuti standar desain brand yang dipilih
                @endif
                <span class="text-xs text-slate-400 block">Atur standarnya di Kelola Brand &rarr; Tampilan.</span>
            </p>
        </div>

        <div x-show="desainTema === 'kop-gambar'" x-cloak>
            <x-input-label for="kop_image" value="Gambar Kop Surat" />
            <input id="kop_image" name="kop_image" type="file" accept="image/*"
                class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-navy-50 file:text-navy-600 hover:file:bg-navy-100">
            @if ($invoice && ! empty($invoice->kop_config['custom_image_path'] ?? null))
                <img src="{{ \Illuminate\Support\Facades\Storage::url($invoice->kop_config['custom_image_path']) }}" alt="Kop surat" class="mt-2 h-16 rounded border border-slate-200 bg-white object-contain">
            @endif
            <x-input-error :messages="$errors->get('kop_image')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="qris_image" value="QRIS Khusus Invoice Ini (opsional)" />
            <input id="qris_image" name="qris_image" type="file" accept="image/*"
                class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-navy-50 file:text-navy-600 hover:file:bg-navy-100">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai QRIS default brand.</p>
            <x-input-error :messages="$errors->get('qris_image')" class="mt-1" />
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6 bg-sky-50/50 -mx-6 px-6 py-6">
        <label class="flex items-center gap-2 text-sm font-semibold text-sky-700 cursor-pointer">
            <input type="hidden" name="sph_aktif" value="0">
            <input type="checkbox" name="sph_aktif" value="1" x-model="sphAktif" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
            Tambahkan SPH (Surat Penawaran Harga) sebelum Invoice
        </label>

        <div x-show="sphAktif" x-cloak class="mt-4 space-y-4 bg-white rounded-lg p-4 border border-sky-200">
            <button type="button" @click="generateSphNarasi()" class="text-sm text-sky-600 hover:text-sky-800 font-medium">
                Generate Otomatis
            </button>

            <div>
                <x-input-label for="sph_perihal" value="Perihal / Judul Penawaran" />
                <x-text-input id="sph_perihal" name="sph_perihal" type="text" class="mt-1 block w-full" x-model="sphPerihal" />
                <x-input-error :messages="$errors->get('sph_perihal')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="sph_narasi" value="Narasi / Isi Penawaran" />
                <textarea id="sph_narasi" name="sph_narasi" rows="8" x-model="sphNarasi"
                    class="mt-1 block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                <x-input-error :messages="$errors->get('sph_narasi')" class="mt-1" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="sph_tempat_tanggal" value="Tempat, Tanggal Surat" />
                    <x-text-input id="sph_tempat_tanggal" name="sph_tempat_tanggal" type="text" class="mt-1 block w-full"
                        value="{{ old('sph_tempat_tanggal', $sph['tempat_tanggal'] ?? '') }}" placeholder="Kediri, 01 Januari 2026" />
                    <x-input-error :messages="$errors->get('sph_tempat_tanggal')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="sph_pengirim" value="Hormat Kami / Nama Pengirim" />
                    <x-text-input id="sph_pengirim" name="sph_pengirim" type="text" class="mt-1 block w-full"
                        value="{{ old('sph_pengirim', $sph['pengirim'] ?? '') }}" />
                    <x-input-error :messages="$errors->get('sph_pengirim')" class="mt-1" />
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <h3 class="text-sm font-semibold text-slate-700">Item Invoice</h3>
            <div class="flex items-center gap-4">
                <select @change="loadPaket($event.target.value); $event.target.value = ''"
                    class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">⚡ Muat dari Paket...</option>
                    @foreach (config('invoice_packages') as $key => $paket)
                        <option value="{{ $key }}">{{ $paket['label'] }}</option>
                    @endforeach
                </select>
                <button type="button" @click="addGroup()" class="text-sm text-sky-600 hover:text-sky-800 font-medium">
                    + Sub Tambah Item
                </button>
                <button type="button" @click="addItem()" class="text-sm text-navy-500 hover:text-navy-700 font-medium">
                    + Tambah Item
                </button>
            </div>
        </div>
        <x-input-error :messages="$errors->get('items')" class="mb-2" />

        <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
            <div class="sm:col-span-4">Deskripsi Pekerjaan</div>
            <div class="sm:col-span-1">Volume</div>
            <div class="sm:col-span-2">Satuan</div>
            <div class="sm:col-span-2">Harga Satuan</div>
            <div class="sm:col-span-2">Jumlah</div>
            <div class="sm:col-span-1"></div>
        </div>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="item._uid">
                <div class="rounded-lg p-3"
                     :class="item.type === 'paket' ? 'bg-amber-50 border-l-4 border-amber-400' : (item.type === 'group' ? 'bg-sky-50 border-l-4 border-sky-400' : 'bg-slate-50')">

                    <input type="hidden" :name="'items[' + index + '][id]'" x-model="item.id">
                    <input type="hidden" :name="'items[' + index + '][type]'" x-model="item.type">

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                        <div class="sm:col-span-4">
                            <template x-if="item.type === 'paket'">
                                <textarea :name="'items[' + index + '][deskripsi]'" x-model="item.deskripsi" rows="4"
                                    class="block w-full text-xs font-mono border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            </template>
                            <template x-if="item.type !== 'paket'">
                                <input type="text" :name="'items[' + index + '][deskripsi]'" x-model="item.deskripsi"
                                    :placeholder="item.type === 'group' ? 'Nama Sub Item / Grup' : 'Deskripsi item'"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            </template>
                        </div>

                        <template x-if="item.type !== 'group'">
                            <div class="sm:col-span-1">
                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][volume]'" x-model="item.volume"
                                    placeholder="Volume"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            </div>
                        </template>
                        <template x-if="item.type !== 'group'">
                            <div class="sm:col-span-2">
                                <input type="text" :name="'items[' + index + '][satuan]'" x-model="item.satuan"
                                    placeholder="Satuan"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                        </template>
                        <template x-if="item.type !== 'group'">
                            <div class="sm:col-span-2">
                                <input type="number" step="0.01" min="0" :name="'items[' + index + '][harga_satuan]'" x-model="item.harga_satuan"
                                    placeholder="Harga satuan"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            </div>
                        </template>
                        <template x-if="item.type !== 'group'">
                            <div class="sm:col-span-2 flex items-center h-[38px] text-sm font-semibold text-slate-700">
                                <span x-text="'Rp ' + itemTotal(item).toLocaleString('id-ID')"></span>
                            </div>
                        </template>

                        <template x-if="item.type === 'group'">
                            <div class="sm:col-span-7 flex items-center justify-end text-sm font-semibold text-sky-700">
                                <span x-text="item.sub_items.length + ' sub item — Rp ' + itemTotal(item).toLocaleString('id-ID')"></span>
                            </div>
                        </template>

                        <div class="sm:col-span-1 flex justify-end">
                            <button type="button" @click="removeItem(item._uid)" class="text-red-500 hover:text-red-700 text-sm">
                                &times;
                            </button>
                        </div>
                    </div>

                    <template x-if="item.type === 'group'">
                        <div class="mt-3 pl-4 space-y-2 border-l-2 border-sky-200">
                            <input type="hidden" :name="'items[' + index + '][volume]'" value="1">
                            <input type="hidden" :name="'items[' + index + '][harga_satuan]'" value="0">

                            <template x-for="(sub, subIndex) in item.sub_items" :key="sub._uid">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-start">
                                    <input type="hidden" :name="'items[' + index + '][sub_items][' + subIndex + '][id]'" x-model="sub.id">
                                    <div class="sm:col-span-5">
                                        <input type="text" :name="'items[' + index + '][sub_items][' + subIndex + '][deskripsi]'" x-model="sub.deskripsi"
                                            placeholder="Nama sub item"
                                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="number" step="0.01" min="0" :name="'items[' + index + '][sub_items][' + subIndex + '][volume]'" x-model="sub.volume"
                                            placeholder="Volume"
                                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="text" :name="'items[' + index + '][sub_items][' + subIndex + '][satuan]'" x-model="sub.satuan"
                                            placeholder="Satuan"
                                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="number" step="0.01" min="0" :name="'items[' + index + '][sub_items][' + subIndex + '][harga_satuan]'" x-model="sub.harga_satuan"
                                            placeholder="Harga satuan"
                                            class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    </div>
                                    <div class="sm:col-span-1 flex justify-end">
                                        <button type="button" @click="removeSubItem(item, sub._uid)" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addSubItem(item)" class="text-xs text-sky-600 hover:text-sky-800 font-medium">
                                + Tambah Sub Item
                            </button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6" x-effect="recalcTerms()">
        <div class="flex items-center justify-between mb-2 flex-wrap gap-3">
            <div class="flex items-center gap-3 flex-wrap">
                <h3 class="text-sm font-semibold text-slate-700">Termin Pembayaran</h3>
                <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
                    <input type="checkbox" x-model="terminAktif" @change="toggleTerminAktif()" class="rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                    Aktifkan Termin
                </label>
            </div>
            <div class="flex items-center gap-3" x-show="terminAktif" x-cloak>
                <select @change="loadTerminPreset($event.target.value); $event.target.value = ''"
                    class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">⚡ Preset Termin...</option>
                    <option value="2-50-50">2 Termin (50% - 50%)</option>
                    <option value="2-30-70">2 Termin (30% - 70%)</option>
                    <option value="3-30-30-40">3 Termin (30% - 30% - 40%)</option>
                    <option value="3-40-30-30">3 Termin (40% - 30% - 30%)</option>
                    <option value="3-33-33-34">3 Termin (33% - 33% - 34%)</option>
                    <option value="4-25-25-25-25">4 Termin (25% - 25% - 25% - 25%)</option>
                    <option value="custom">⚙️ Termin Custom (nominal bebas)</option>
                </select>
                <button type="button" @click="addTerm()" class="text-sm text-navy-500 hover:text-navy-700 font-medium">
                    + Tambah Termin
                </button>
            </div>
        </div>

        <div x-show="terminAktif" x-cloak>
            <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer mb-4">
                <input type="hidden" name="termin_show_pct" value="0">
                <input type="checkbox" name="termin_show_pct" value="1" x-model="terminShowPct" class="rounded border-gray-300 text-navy-600 focus:ring-navy-500">
                Tampilkan Persentase (%) pada invoice
            </label>

            <x-input-error :messages="$errors->get('terms')" class="mb-2" />

            <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-1 mb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wide">
                <div class="sm:col-span-1 text-center">Lunas</div>
                <div class="sm:col-span-3">Label</div>
                <div class="sm:col-span-2">Persen</div>
                <div class="sm:col-span-2">Nominal</div>
                <div class="sm:col-span-3">Tgl Bayar</div>
                <div class="sm:col-span-1"></div>
            </div>

            <div class="space-y-3">
                <template x-for="(term, index) in terms" :key="term._uid">
                    <div class="bg-slate-50 rounded-lg p-3">
                        <input type="hidden" :name="'terms[' + index + '][id]'" x-model="term.id">

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <div class="sm:col-span-1 flex justify-center">
                                <input type="checkbox" x-model="term.is_lunas" title="Tandai sudah lunas"
                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <input type="hidden" :name="'terms[' + index + '][is_lunas]'" :value="term.is_lunas ? 1 : 0">
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" :name="'terms[' + index + '][label]'" x-model="term.label"
                                    placeholder="Label termin (misal: DP 50%)"
                                    :class="term.is_lunas ? 'line-through text-slate-400' : ''"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" step="0.01" min="0" max="100" :name="'terms[' + index + '][persen]'" x-model="term.persen"
                                    placeholder="Persen"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            </div>
                            <div class="sm:col-span-2">
                                <input type="number" step="0.01" min="0" :name="'terms[' + index + '][nominal]'" x-model="term.nominal"
                                    placeholder="Nominal"
                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                            <div class="sm:col-span-3">
                                <input type="date" :name="'terms[' + index + '][tanggal_lunas]'" x-model="term.tanggal_lunas"
                                    title="Tanggal klien membayar termin ini"
                                    class="block w-full text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            </div>
                            <div class="sm:col-span-1 flex justify-end">
                                <button type="button" @click="removeTerm(term._uid)" class="text-red-500 hover:text-red-700 text-sm">
                                    &times;
                                </button>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1 ml-1" x-show="parseFloat(term.persen) > 0">Nominal otomatis dihitung dari persen terhadap total invoice</p>

                        <div class="mt-2">
                            <textarea :name="'terms[' + index + '][catatan]'" x-model="term.catatan" rows="2"
                                placeholder="Catatan / deskripsi termin ini (opsional, misal: syarat pencairan atau rincian pekerjaan)"
                                class="block w-full text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-6">
        <p class="text-xs text-slate-400">
            Rekening pembayaran &amp; tanda tangan/stempel/e-materai brand akan otomatis disertakan di invoice ini.
            Kelola dari halaman <a href="{{ route('brands.index') }}" class="text-navy-500 hover:underline">Kelola Brand</a>.
        </p>
    </div>

    <div class="border-t border-slate-200 pt-6 flex justify-end">
        <dl class="w-full sm:w-64 space-y-1 text-sm">
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">Diskon (%)</dt>
                <dd>
                    <input type="number" name="diskon_persen" step="0.01" min="0" max="100" x-model="diskon"
                        class="w-20 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                </dd>
            </div>
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">PPN (%)</dt>
                <dd>
                    <input type="number" name="ppn_persen" step="0.01" min="0" max="100" x-model="ppn"
                        class="w-20 text-right text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                </dd>
            </div>
            <div class="flex justify-between pt-1 border-t border-slate-200">
                <dt class="text-slate-500">Subtotal</dt>
                <dd class="font-medium text-slate-700" x-text="'Rp ' + subtotal().toLocaleString('id-ID')"></dd>
            </div>
            <div class="flex justify-between text-base font-bold text-navy-600 pt-1 border-t border-slate-200">
                <dt>Total</dt>
                <dd x-text="'Rp ' + total().toLocaleString('id-ID')"></dd>
            </div>
        </dl>
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="catatan" value="Catatan" />
        <textarea id="catatan" name="catatan" rows="2"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('catatan', $invoice->catatan ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('catatan')" class="mt-1" />
    </div>

    <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
        <x-primary-button>Simpan Invoice</x-primary-button>
        <x-secondary-button type="submit" formaction="{{ route('invoices.preview') }}" formtarget="_blank">
            👁 Preview
        </x-secondary-button>
        <a href="{{ $invoice ? route('invoices.show', $invoice) : route('invoices.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
            Batal
        </a>
    </div>
</div>
