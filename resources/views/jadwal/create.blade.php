<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Buat Jadwal Perencanaan Baru</h2>
                <p class="text-sm text-slate-500 mt-0.5">Tentukan parameter proyek dan rincian tahapan pekerjaan</p>
            </div>
            <a href="{{ route('jadwal-perencanaan.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div x-data="{
        durasi: 20,
        items: [
            { nama_item: 'Pekerjaan Persiapan & Survei', bobot: 10, hari_mulai: 1, hari_selesai: 3 },
            { nama_item: 'Konsep Desain & Denah Awal', bobot: 30, hari_mulai: 4, hari_selesai: 10 },
            { nama_item: 'Pemodelan 3D & Rendering', bobot: 40, hari_mulai: 11, hari_selesai: 17 },
            { nama_item: 'Finalisasi Gambar Kerja & Serah Terima', bobot: 20, hari_mulai: 18, hari_selesai: 20 }
        ],
        addItem() {
            this.items.push({
                nama_item: '',
                bobot: 0,
                hari_mulai: 1,
                hari_selesai: this.durasi
            });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        get totalBobot() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.bobot) || 0), 0).toFixed(2);
        },
        get is100Percent() {
            return Math.abs(parseFloat(this.totalBobot) - 100.00) <= 0.01;
        }
    }">
        <form action="{{ route('jadwal-perencanaan.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Project Header Info --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-navy-700 border-b border-slate-100 pb-3">Parameter Proyek</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Brand <span class="text-rose-500">*</span></label>
                        <select name="brand_id" required class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                            <option value="">Pilih Brand</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Nama Proyek <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_proyek" value="{{ old('nama_proyek') }}" required placeholder="Contoh: Pembangunan Villa Ubud"
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                        @error('nama_proyek') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Lokasi Proyek</label>
                        <input type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Seminyak, Bali"
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                        @error('lokasi') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Invoice Referensi (Opsional)</label>
                        <select name="invoice_id" class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                            <option value="">Tanpa Referensi Invoice</option>
                            @foreach ($invoices as $inv)
                                <option value="{{ $inv->id }}" @selected(old('invoice_id') == $inv->id)>
                                    {{ $inv->nomor }} &mdash; {{ $inv->klien_nama }} (Rp {{ number_format($inv->total, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Tanggal Mulai Proyek <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                        @error('tanggal_mulai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Durasi Proyek (Hari) <span class="text-rose-500">*</span></label>
                        <input type="number" name="durasi_hari" x-model.number="durasi" min="1" max="365" required
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                        @error('durasi_hari') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan internal atau catatan khusus jadwal..."
                              class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">{{ old('catatan') }}</textarea>
                </div>
            </div>

            {{-- Items Repeater --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-navy-700">Tahapan Pekerjaan & Bobot</h3>
                        <p class="text-xs text-slate-500">Tentukan nama tahapan, rentang hari pelaksanaan (1 s/d <span x-text="durasi"></span>), dan bobot (%)</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-xs font-semibold px-3 py-1.5 rounded-lg border flex items-center gap-1.5"
                             :class="is100Percent ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                            <span>Total Bobot:</span>
                            <span class="font-extrabold text-sm" x-text="totalBobot + '%'"></span>
                            <span x-show="is100Percent" class="text-xs font-bold text-emerald-600">(100% Valid)</span>
                            <span x-show="!is100Percent" class="text-xs font-bold text-amber-600">(Belum 100%)</span>
                        </div>

                        <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-navy-600 hover:bg-navy-700 text-white transition shadow-sm">
                            <x-icon name="plus-circle" class="w-3.5 h-3.5" />
                            Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                                <th class="text-center w-12 px-3 py-2.5">No</th>
                                <th class="text-left px-3 py-2.5">Nama Tahapan Pekerjaan</th>
                                <th class="text-center w-28 px-3 py-2.5">Bobot (%)</th>
                                <th class="text-center w-24 px-3 py-2.5">Mulai (H-)</th>
                                <th class="text-center w-24 px-3 py-2.5">Selesai (H-)</th>
                                <th class="text-center w-20 px-3 py-2.5">Durasi</th>
                                <th class="text-center w-12 px-3 py-2.5">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="text-center font-bold text-slate-400 py-2.5" x-text="index + 1"></td>
                                    <td class="px-3 py-2.5">
                                        <input type="text" :name="'items[' + index + '][nama_item]'" x-model="item.nama_item" required
                                               placeholder="Uraian pekerjaan..."
                                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-md shadow-sm">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" step="0.01" min="0" max="100" :name="'items[' + index + '][bobot]'" x-model.number="item.bobot" required
                                               class="w-full text-center text-sm font-semibold border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-md shadow-sm">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" min="1" :max="durasi" :name="'items[' + index + '][hari_mulai]'" x-model.number="item.hari_mulai" required
                                               class="w-full text-center text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-md shadow-sm">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <input type="number" min="1" :max="durasi" :name="'items[' + index + '][hari_selesai]'" x-model.number="item.hari_selesai" required
                                               class="w-full text-center text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-md shadow-sm">
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-semibold text-slate-600 text-xs"
                                        x-text="Math.max(1, (item.hari_selesai - item.hari_mulai + 1)) + ' Hari'">
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 p-1">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Submit Footer --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('jadwal-perencanaan.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-800 font-bold text-sm px-6 py-2.5 rounded-lg transition shadow">
                    Simpan Jadwal Perencanaan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
