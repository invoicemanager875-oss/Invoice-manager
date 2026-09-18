<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Jadwal Perencanaan & Kurva S</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manajemen linimasa tahapan pekerjaan dan distribusi Kurva S proyek</p>
            </div>

            @can('create', \App\Models\JadwalPerencanaan::class)
                <a href="{{ route('jadwal-perencanaan.create') }}"
                   class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition shadow-sm">
                    <x-icon name="plus-circle" class="w-4 h-4" />
                    Buat Jadwal Baru
                </a>
            @endcan
        </div>
    </x-slot>

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-4 flex flex-wrap items-end gap-3 shadow-sm">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Cari Proyek / Lokasi</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama proyek atau lokasi..."
                   class="w-full text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Brand</label>
            <select name="brand_id" class="text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                <option value="">Semua Brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status</label>
            <select name="status" class="text-sm border-slate-300 focus:border-navy-500 focus:ring-navy-500 rounded-lg shadow-sm">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="archived" @selected(request('status') === 'archived')>Archived</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('jadwal-perencanaan.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-2 py-2">Reset</a>
        </div>
    </form>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <th class="text-left px-5 py-3">Nama Proyek</th>
                        <th class="text-left px-5 py-3">Brand</th>
                        <th class="text-left px-5 py-3">Durasi</th>
                        <th class="text-left px-5 py-3">Periode</th>
                        <th class="text-center px-5 py-3">Total Bobot</th>
                        <th class="text-center px-5 py-3">Status</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('jadwal-perencanaan.show', $jadwal) }}" class="font-bold text-navy-700 hover:text-navy-900 block">
                                    {{ $jadwal->nama_proyek }}
                                </a>
                                @if ($jadwal->lokasi)
                                    <span class="text-xs text-slate-400 block">{{ $jadwal->lokasi }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $jadwal->brand->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-slate-700">
                                {{ $jadwal->durasi_hari }} Hari
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ $jadwal->tanggal_mulai ? $jadwal->tanggal_mulai->format('d/m/Y') : '-' }} &mdash;
                                {{ $jadwal->tanggal_selesai ? $jadwal->tanggal_selesai->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($jadwal->is_bobot_valid)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        {{ number_format($jadwal->total_bobot, 2) }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ number_format($jadwal->total_bobot, 2) }}% (Belum 100%)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($jadwal->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Published
                                    </span>
                                @elseif ($jadwal->status === 'archived')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Archived
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('jadwal-perencanaan.show', $jadwal) }}"
                                       class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        Detail
                                    </a>
                                    @can('update', $jadwal)
                                        <a href="{{ route('jadwal-perencanaan.edit', $jadwal) }}"
                                           class="px-2.5 py-1 text-xs font-semibold rounded bg-navy-50 hover:bg-navy-100 text-navy-700 transition">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400 text-sm">
                                Belum ada jadwal perencanaan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jadwals->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $jadwals->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
