<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Surat Perjanjian Kerja (SPK)</h2>
                <p class="text-sm text-slate-500 mt-0.5">Manajemen dokumen kontrak legalitas kerja proyek per brand</p>
            </div>

            @can('create', \App\Models\Contract::class)
                <a href="{{ route('contracts.create') }}"
                   class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition shadow-sm">
                    <x-icon name="plus-circle" class="w-4 h-4" />
                    Buat SPK Baru
                </a>
            @endcan
        </div>
    </x-slot>

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-4 flex flex-wrap items-end gap-3 shadow-sm">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Cari No SPK / Klien / Judul</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor, klien, atau judul..."
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
                <option value="final" @selected(request('status') === 'final')>Final</option>
                <option value="signed" @selected(request('status') === 'signed')>Signed</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="void" @selected(request('status') === 'void')>Void</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('contracts.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-2 py-2">Reset</a>
        </div>
    </form>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <th class="text-left px-5 py-3">Nomor SPK</th>
                        <th class="text-left px-5 py-3">Brand</th>
                        <th class="text-left px-5 py-3">Pihak Kedua (Klien)</th>
                        <th class="text-right px-5 py-3">Nilai Kontrak</th>
                        <th class="text-left px-5 py-3">Durasi</th>
                        <th class="text-center px-5 py-3">Status</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($contracts as $contract)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('contracts.show', $contract) }}" class="font-bold text-navy-700 hover:text-navy-900 block font-mono">
                                    {{ $contract->nomor }}
                                </a>
                                <span class="text-xs text-slate-400 block">{{ $contract->tanggal_kontrak ? $contract->tanggal_kontrak->format('d M Y') : '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $contract->brand->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-slate-800 block">{{ $contract->pihak_kedua_nama }}</span>
                                <span class="text-xs text-slate-400 block truncate max-w-[200px]">{{ $contract->pihak_kedua_telepon }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-navy-800">
                                Rp {{ number_format($contract->nilai_kontrak, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600">
                                <div><strong>{{ $contract->durasi_hari }}</strong> Hari Kalender</div>
                                <span class="text-slate-400 text-[11px]">{{ $contract->tanggal_mulai ? $contract->tanggal_mulai->format('d/m/Y') : '' }} - {{ $contract->tanggal_selesai ? $contract->tanggal_selesai->format('d/m/Y') : '' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if ($contract->status === 'final')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        Final
                                    </span>
                                @elseif ($contract->status === 'signed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Signed
                                    </span>
                                @elseif ($contract->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Active
                                    </span>
                                @elseif ($contract->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Completed
                                    </span>
                                @elseif ($contract->status === 'void')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Void
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('contracts.show', $contract) }}"
                                       class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        Detail
                                    </a>
                                    @can('update', $contract)
                                        <a href="{{ route('contracts.edit', $contract) }}"
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
                                Belum ada surat perjanjian kerja (SPK) yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contracts->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
