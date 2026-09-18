<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-navy-600">Edit SPK: {{ $contract->nomor }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase">
                        {{ $contract->status }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">Perbarui rincian komparisi, lingkup pekerjaan, termin, atau teks narasi perjanjian</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('contracts.show', $contract) }}" class="text-sm text-slate-500 hover:text-slate-700">
                    &larr; Batal & Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $brandsData = $brands->keyBy('id')->map(function ($b) {
            return [
                'id' => $b->id,
                'name' => $b->name,
                'address' => $b->address ?? '',
                'phone' => $b->phone ?? '',
                'ttd_nama' => $b->ttd_nama ?? 'Pimpinan Brand',
                'ttd_jabatan' => $b->ttd_jabatan ?? 'Direktur',
                'rekening_config' => $b->rekening_config ?? [],
            ];
        });

        $contractScopes = $contract->scopes->map(function ($s) {
            return [
                'nama_paket' => $s->nama_paket,
                'deskripsi' => $s->deskripsi ?? '',
                'nominal' => (float) $s->nominal,
            ];
        })->values()->toArray();

        $contractTerms = $contract->terms->map(function ($t) {
            return [
                'judul' => $t->judul,
                'persentase' => (float) $t->persentase,
                'nominal' => (float) $t->nominal,
                'syarat_pencairan' => $t->syarat_pencairan ?? '',
            ];
        })->values()->toArray();

        if (empty($contractScopes)) {
            $contractScopes = [['nama_paket' => '', 'deskripsi' => '', 'nominal' => 0]];
        }
        if (empty($contractTerms)) {
            $contractTerms = [['judul' => 'Termin I', 'persentase' => 100, 'nominal' => $contract->nilai_kontrak, 'syarat_pencairan' => '']];
        }
    @endphp

    <div x-data="{
        brands: {{ Js::from($brandsData) }},
        brandId: '{{ old('brand_id', $contract->brand_id) }}',
        p1Nama: '{{ addslashes(old('pihak_pertama_nama', $contract->pihak_pertama_nama)) }}',
        p1Jabatan: '{{ addslashes(old('pihak_pertama_jabatan', $contract->pihak_pertama_jabatan)) }}',
        p1Perusahaan: '{{ addslashes(old('pihak_pertama_perusahaan', $contract->pihak_pertama_perusahaan)) }}',
        p1Alamat: '{{ addslashes(old('pihak_pertama_alamat', $contract->pihak_pertama_alamat)) }}',
        p1Telepon: '{{ addslashes(old('pihak_pertama_telepon', $contract->pihak_pertama_telepon)) }}',

        p2Nama: '{{ addslashes(old('pihak_kedua_nama', $contract->pihak_kedua_nama)) }}',
        p2Alamat: '{{ addslashes(old('pihak_kedua_alamat', $contract->pihak_kedua_alamat)) }}',
        p2Telepon: '{{ addslashes(old('pihak_kedua_telepon', $contract->pihak_kedua_telepon)) }}',

        nilaiKontrak: {{ old('nilai_kontrak', $contract->nilai_kontrak) }},
        durasiHari: {{ old('durasi_hari', $contract->durasi_hari) }},
        tglMulai: '{{ old('tanggal_mulai', $contract->tanggal_mulai?->format('Y-m-d') ?? '') }}',
        tglSelesai: '{{ old('tanggal_selesai', $contract->tanggal_selesai?->format('Y-m-d') ?? '') }}',
        narasi: `{{ addslashes(old('narasi', $contract->narasi)) }}`,

        scopes: {{ Js::from(old('scopes', $contractScopes)) }},
        terms: {{ Js::from(old('terms', $contractTerms)) }},

        onBrandChange() {
            let b = this.brands[this.brandId];
            if (b) {
                this.p1Nama = b.ttd_nama || 'Pimpinan Brand';
                this.p1Jabatan = b.ttd_jabatan || 'Direktur';
                this.p1Perusahaan = b.name;
                this.p1Alamat = b.address || '';
                this.p1Telepon = b.phone || '';
            }
        },
        updateEndDate() {
            if (this.tglMulai && this.durasiHari > 0) {
                let start = new Date(this.tglMulai);
                start.setDate(start.getDate() + parseInt(this.durasiHari) - 1);
                this.tglSelesai = start.toISOString().split('T')[0];
            }
        },
        addScope() {
            this.scopes.push({ nama_paket: '', deskripsi: '', nominal: 0 });
        },
        removeScope(index) {
            this.scopes.splice(index, 1);
        },
        addTerm() {
            this.terms.push({ judul: 'Termin ' + (this.terms.length + 1), persentase: 0, nominal: 0, syarat_pencairan: '' });
        },
        removeTerm(index) {
            this.terms.splice(index, 1);
        },
        async generateNarasi() {
            let res = await fetch('{{ route('contracts.autoNarasi') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    brand_name: this.p1Perusahaan,
                    pihak_pertama_perusahaan: this.p1Perusahaan,
                    pihak_pertama_alamat: this.p1Alamat,
                    pihak_pertama_telepon: this.p1Telepon,
                    pihak_kedua_nama: this.p2Nama,
                    pihak_kedua_alamat: this.p2Alamat,
                    pihak_kedua_telepon: this.p2Telepon,
                    nilai_kontrak: this.nilaiKontrak,
                    scopes: this.scopes,
                    tanggal_mulai: this.tglMulai,
                    tanggal_selesai: this.tglSelesai,
                    terms: this.terms,
                    rekening_config: (this.brands[this.brandId] ? this.brands[this.brandId].rekening_config : [])
                })
            });
            let data = await res.json();
            if (data.narasi) {
                this.narasi = data.narasi;
            }
        }
    }">
        <form action="{{ route('contracts.update', $contract) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Parameter Kontrak & Brand --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-navy-700 border-b border-slate-100 pb-3">1. Parameter Perjanjian</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Nomor SPK</label>
                        <input type="text" value="{{ $contract->nomor }}" disabled
                               class="w-full text-sm bg-slate-100 text-slate-600 font-mono font-bold border-slate-300 rounded-lg shadow-sm cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Brand <span class="text-rose-500">*</span></label>
                        <select name="brand_id" x-model="brandId" @change="onBrandChange()" required
                                class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Judul Perjanjian <span class="text-rose-500">*</span></label>
                        <input type="text" name="judul_kontrak" value="{{ old('judul_kontrak', $contract->judul_kontrak) }}" required
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Tanggal Perjanjian <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_kontrak" value="{{ old('tanggal_kontrak', $contract->tanggal_kontrak?->format('Y-m-d')) }}" required
                               class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                    </div>
                </div>
            </div>

            {{-- Komparisi Para Pihak --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Pihak Pertama --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-navy-700 uppercase tracking-wide border-b border-slate-100 pb-2">
                        Pihak Pertama (Penyedia Jasa / Brand)
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Perusahaan / Studio</label>
                            <input type="text" name="pihak_pertama_perusahaan" x-model="p1Perusahaan" required
                                   class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Penandatangan</label>
                                <input type="text" name="pihak_pertama_nama" x-model="p1Nama" required
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Jabatan</label>
                                <input type="text" name="pihak_pertama_jabatan" x-model="p1Jabatan" required
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Alamat Kantor</label>
                            <textarea name="pihak_pertama_alamat" x-model="p1Alamat" rows="2" required
                                      class="w-full text-sm border-slate-300 rounded-lg shadow-sm"></textarea>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nomor Telepon</label>
                            <input type="text" name="pihak_pertama_telepon" x-model="p1Telepon"
                                   class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Pihak Kedua --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-navy-700 uppercase tracking-wide border-b border-slate-100 pb-2">
                        Pihak Kedua (Pengguna Jasa / Klien)
                    </h3>
                    <div class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Klien <span class="text-rose-500">*</span></label>
                                <input type="text" name="pihak_kedua_nama" x-model="p2Nama" required
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">No. KTP / NIK / NPWP</label>
                                <input type="text" name="pihak_kedua_identitas" value="{{ old('pihak_kedua_identitas', $contract->pihak_kedua_identitas) }}"
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Perusahaan (Opsional)</label>
                            <input type="text" name="pihak_kedua_perusahaan" value="{{ old('pihak_kedua_perusahaan', $contract->pihak_kedua_perusahaan) }}"
                                   class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Alamat Klien <span class="text-rose-500">*</span></label>
                            <textarea name="pihak_kedua_alamat" x-model="p2Alamat" rows="2" required
                                      class="w-full text-sm border-slate-300 rounded-lg shadow-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nomor Telepon / WhatsApp <span class="text-rose-500">*</span></label>
                                <input type="text" name="pihak_kedua_telepon" x-model="p2Telepon" required
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Email Klien</label>
                                <input type="email" name="pihak_kedua_email" value="{{ old('pihak_kedua_email', $contract->pihak_kedua_email) }}"
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nilai & Waktu Pelaksanaan --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-navy-700 border-b border-slate-100 pb-3">2. Nilai Kontrak & Jadwal Pelaksanaan</h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Nilai Kontrak Total (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="nilai_kontrak" x-model.number="nilaiKontrak" required
                               class="w-full text-base font-bold text-navy-800 border-slate-300 rounded-lg shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_mulai" x-model="tglMulai" @change="updateEndDate()" required
                               class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1">Durasi (Hari) <span class="text-rose-500">*</span></label>
                        <input type="number" name="durasi_hari" x-model.number="durasiHari" @change="updateEndDate()" min="1" max="365" required
                               class="w-full text-sm border-slate-300 rounded-lg shadow-sm">
                    </div>
                </div>
                <input type="hidden" name="tanggal_selesai" x-model="tglSelesai">
            </div>

            {{-- Scopes Repeater --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-navy-700">3. Lingkup Pekerjaan (Pasal 1)</h3>
                    <button type="button" @click="addScope()"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg bg-navy-600 text-white hover:bg-navy-700 transition">
                        + Tambah Paket
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(scope, idx) in scopes" :key="idx">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex items-start gap-3">
                            <div class="w-8 pt-2 text-center font-bold text-slate-400" x-text="idx + 1"></div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Nama Paket Pekerjaan</label>
                                    <input type="text" :name="'scopes[' + idx + '][nama_paket]'" x-model="scope.nama_paket" required
                                           placeholder="Misal: Perencanaan Arsitektur"
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Rincian Deliverable</label>
                                    <input type="text" :name="'scopes[' + idx + '][deskripsi]'" x-model="scope.deskripsi"
                                           placeholder="3D Render, Gambar Kerja..."
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Nominal Subtotal (Rp)</label>
                                    <input type="number" step="0.01" :name="'scopes[' + idx + '][nominal]'" x-model.number="scope.nominal"
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            <button type="button" @click="removeScope(idx)" class="pt-2 text-rose-500 hover:text-rose-700">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Terms Repeater --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-navy-700">4. Jadwal Termin Pembayaran (Pasal 3)</h3>
                    <button type="button" @click="addTerm()"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg bg-navy-600 text-white hover:bg-navy-700 transition">
                        + Tambah Termin
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(term, idx) in terms" :key="idx">
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex items-start gap-3">
                            <div class="w-8 pt-2 text-center font-bold text-slate-400" x-text="idx + 1"></div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Judul Termin</label>
                                    <input type="text" :name="'terms[' + idx + '][judul]'" x-model="term.judul" required
                                           placeholder="Termin I (DP)"
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Persen (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" :name="'terms[' + idx + '][persentase]'" x-model.number="term.persentase" required
                                           @input="term.nominal = Math.round(nilaiKontrak * (term.persentase / 100))"
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Nominal (Rp)</label>
                                    <input type="number" step="0.01" :name="'terms[' + idx + '][nominal]'" x-model.number="term.nominal" required
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Syarat Pencairan</label>
                                    <input type="text" :name="'terms[' + idx + '][syarat_pencairan]'" x-model="term.syarat_pencairan"
                                           placeholder="Setelah gambar selesai..."
                                           class="w-full text-sm border-slate-300 rounded-md shadow-sm">
                                </div>
                            </div>
                            <button type="button" @click="removeTerm(idx)" class="pt-2 text-rose-500 hover:text-rose-700">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Narasi Pasal Editor --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-navy-700">5. Teks Batang Tubuh Pasal Perjanjian</h3>
                        <p class="text-xs text-slate-500">Draf narasi hukum lengkap, dapat disunting bebas atau di-generate otomatis</p>
                    </div>

                    <button type="button" @click="generateNarasi()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-gold-400 hover:bg-gold-500 text-navy-800 font-bold text-xs transition shadow-sm">
                        ✨ Generate Auto-Narasi
                    </button>
                </div>

                <div>
                    <textarea name="narasi" x-model="narasi" rows="18"
                              class="w-full font-mono text-xs leading-relaxed border-slate-300 rounded-lg focus:border-navy-500 focus:ring-navy-500 shadow-sm"></textarea>
                </div>
            </div>

            {{-- Submit Footer --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('contracts.show', $contract) }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white font-bold text-sm px-6 py-2.5 rounded-lg transition shadow">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
