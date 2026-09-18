<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold text-navy-600">{{ $contract->nomor }}</h2>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $contract->status === 'final' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $contract->status }}
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-0.5">{{ $contract->judul_kontrak }} &mdash; {{ $contract->brand->name ?? $contract->pihak_pertama_perusahaan }}</p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('contracts.pdf', $contract) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm">
                    <x-icon name="printer" class="w-4 h-4" />
                    Unduh / Cetak PDF
                </a>

                @if ($contract->status === 'draft')
                    @can('update', $contract)
                        <a href="{{ route('contracts.edit', $contract) }}"
                           class="inline-flex items-center gap-1.5 bg-gold-400 hover:bg-gold-500 text-navy-800 text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm">
                            <x-icon name="pencil" class="w-4 h-4" />
                            Edit Draf
                        </a>
                    @endcan

                    @can('finalize', $contract)
                        <form action="{{ route('contracts.finalize', $contract) }}" method="POST"
                              onsubmit="return confirm('Apakah Anda yakin ingin memfinalisasi Surat Kontrak ini? Setelah difinalisasi, dokumen akan DIKUNCI secara permanen dan tidak dapat diubah lagi.');">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm">
                                <x-icon name="check" class="w-4 h-4" />
                                Finalisasi Kontrak
                            </button>
                        </form>
                    @endcan

                    @can('delete', $contract)
                        <form action="{{ route('contracts.destroy', $contract) }}" method="POST"
                              onsubmit="return confirm('Hapus draf kontrak {{ $contract->nomor }}? Data yang dihapus tidak dapat dikembalikan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-semibold px-2 py-2">
                                Hapus
                            </button>
                        </form>
                    @endcan
                @endif

                <a href="{{ route('contracts.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2 py-2">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if ($contract->status === 'final')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">
                    ✓
                </div>
                <div class="text-xs text-emerald-800">
                    <span class="font-bold">Kontrak Berstatus FINAL (Terkunci).</span>
                    Dokumen ini telah disetujui secara resmi dan bersifat immutable. Tidak ada perubahan yang dapat dilakukan lagi pada dokumen ini.
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-sm">
                        i
                    </div>
                    <div class="text-xs text-amber-800">
                        <span class="font-bold">Status DRAF.</span>
                        Periksa kembali seluruh butir klausul dan kesepakatan nilai sebelum menekan tombol <strong>Finalisasi Kontrak</strong>.
                    </div>
                </div>
            </div>
        @endif

        {{-- Overview Card --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-start justify-between flex-wrap gap-4 border-b border-slate-100 pb-4">
                <div>
                    <span class="text-xs font-semibold text-gold-600 uppercase tracking-wider">Surat Perjanjian Kerja</span>
                    <h1 class="text-xl font-bold text-navy-800 mt-1">{{ $contract->judul_kontrak }}</h1>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">Nomor: {{ $contract->nomor }}</p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-400">Tanggal Ditandatangani</div>
                    <div class="text-sm font-bold text-navy-700">{{ $contract->tanggal_kontrak?->translatedFormat('d F Y') ?? '-' }}</div>
                    @if ($contract->invoice)
                        <div class="mt-2 text-xs">
                            <span class="text-slate-400">Ref. Invoice:</span>
                            <a href="{{ route('invoices.show', $contract->invoice) }}" class="text-sky-600 hover:underline font-semibold font-mono">
                                {{ $contract->invoice->nomor }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Komparisi Para Pihak --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                    <div class="text-xs font-bold text-navy-700 uppercase tracking-wide border-b border-slate-200 pb-1">
                        Pihak Pertama (Penyedia Jasa)
                    </div>
                    <div class="text-xs space-y-1 text-slate-700">
                        <div><strong class="text-navy-900">{{ $contract->pihak_pertama_perusahaan }}</strong></div>
                        <div>Penandatangan: <strong>{{ $contract->pihak_pertama_nama }}</strong> ({{ $contract->pihak_pertama_jabatan }})</div>
                        <div class="text-slate-500">{{ $contract->pihak_pertama_alamat }}</div>
                        <div class="text-slate-500">Telp: {{ $contract->pihak_pertama_telepon ?? '-' }}</div>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                    <div class="text-xs font-bold text-navy-700 uppercase tracking-wide border-b border-slate-200 pb-1">
                        Pihak Kedua (Pengguna Jasa / Klien)
                    </div>
                    <div class="text-xs space-y-1 text-slate-700">
                        <div><strong class="text-navy-900">{{ $contract->pihak_kedua_nama }}</strong> @if($contract->pihak_kedua_perusahaan) &mdash; {{ $contract->pihak_kedua_perusahaan }} @endif</div>
                        <div>Identitas (KTP/NPWP): <strong>{{ $contract->pihak_kedua_identitas ?: '-' }}</strong></div>
                        <div class="text-slate-500">{{ $contract->pihak_kedua_alamat }}</div>
                        <div class="text-slate-500">Telp/WA: {{ $contract->pihak_kedua_telepon }} | Email: {{ $contract->pihak_kedua_email ?: '-' }}</div>
                    </div>
                </div>
            </div>

            {{-- Nilai & Waktu --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 p-4 rounded-lg bg-navy-900 text-white">
                <div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider">Total Nilai Kontrak</div>
                    <div class="text-xl font-extrabold text-gold-400 mt-0.5">
                        Rp {{ number_format($contract->nilai_kontrak, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-slate-300 italic mt-1 leading-snug">
                        "{{ $contract->nilai_terbilang }}"
                    </div>
                </div>

                <div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider">Durasi Pelaksanaan</div>
                    <div class="text-xl font-bold mt-0.5">
                        {{ $contract->durasi_hari }} Hari Kalender
                    </div>
                    <div class="text-[11px] text-slate-300 mt-1">
                        {{ $contract->tanggal_mulai?->translatedFormat('d M Y') }} s/d {{ $contract->tanggal_selesai?->translatedFormat('d M Y') }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] text-slate-300 uppercase tracking-wider">Brand / Studio</div>
                    <div class="text-base font-bold mt-0.5">
                        {{ $contract->brand->name ?? $contract->pihak_pertama_perusahaan }}
                    </div>
                    <div class="text-[11px] text-slate-300 mt-1">
                        Tercatat oleh: {{ $contract->creator->name ?? 'Sistem' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Lingkup Pekerjaan --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-navy-800">Lingkup Pekerjaan & Deliverables (Pasal 1)</h3>
                <span class="text-xs text-slate-400">{{ $contract->scopes->count() }} Paket Pekerjaan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 w-12 text-center">No</th>
                            <th class="px-6 py-3">Paket Pekerjaan</th>
                            <th class="px-6 py-3">Deskripsi / Detail Rincian</th>
                            <th class="px-6 py-3 text-right">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($contract->scopes as $idx => $scope)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3 font-semibold text-navy-800">{{ $scope->nama_paket }}</td>
                                <td class="px-6 py-3 text-slate-500">{{ $scope->deskripsi ?: '-' }}</td>
                                <td class="px-6 py-3 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($scope->nominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-slate-400">Tidak ada rincian lingkup pekerjaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Termin Pembayaran --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-navy-800">Skema & Termin Pembayaran (Pasal 3)</h3>
                <span class="text-xs text-slate-400">{{ $contract->terms->count() }} Tahapan Termin</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-700 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 w-12 text-center">No</th>
                            <th class="px-6 py-3">Nama Termin</th>
                            <th class="px-6 py-3 text-center">Bobot (%)</th>
                            <th class="px-6 py-3 text-right">Nominal (Rp)</th>
                            <th class="px-6 py-3">Syarat Pencairan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($contract->terms as $idx => $term)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-3 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3 font-semibold text-navy-800">{{ $term->judul }}</td>
                                <td class="px-6 py-3 text-center font-semibold text-slate-700">{{ $term->persentase }}%</td>
                                <td class="px-6 py-3 text-right font-mono font-semibold text-slate-800">
                                    Rp {{ number_format($term->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-slate-500">{{ $term->syarat_pencairan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-slate-400">Tidak ada jadwal termin pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Batang Tubuh Pasal --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-navy-800 border-b border-slate-100 pb-3">Batang Tubuh & Narasi Lengkap Klausul Perjanjian</h3>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-lg text-xs leading-relaxed font-mono whitespace-pre-wrap text-slate-800">
{{ $contract->narasi }}
            </div>
        </div>

        {{-- Signature Block Preview --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-navy-800 border-b border-slate-100 pb-3 mb-6">Blok Penandatanganan Dokumen</h3>
            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <div class="space-y-1">
                    <div class="font-bold text-slate-500 uppercase tracking-wide">Pihak Pertama</div>
                    <div class="font-semibold text-slate-700">{{ $contract->pihak_pertama_perusahaan }}</div>
                    <div class="h-24 flex items-center justify-center text-slate-300 italic text-[11px]">
                        (Tanda Tangan & Stempel Perusahaan)
                    </div>
                    <div class="font-bold text-navy-900 underline">{{ $contract->pihak_pertama_nama }}</div>
                    <div class="text-slate-500">{{ $contract->pihak_pertama_jabatan }}</div>
                </div>

                <div class="space-y-1">
                    <div class="font-bold text-slate-500 uppercase tracking-wide">Pihak Kedua</div>
                    <div class="font-semibold text-slate-700">{{ $contract->pihak_kedua_perusahaan ?: 'Pengguna Jasa' }}</div>
                    <div class="h-24 flex items-center justify-center text-slate-400 border border-dashed border-slate-200 rounded mx-auto w-32 text-[10px]">
                        Materai Rp 10.000
                    </div>
                    <div class="font-bold text-navy-900 underline">{{ $contract->pihak_kedua_nama }}</div>
                    <div class="text-slate-500">Klien / Pemilik Proyek</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
