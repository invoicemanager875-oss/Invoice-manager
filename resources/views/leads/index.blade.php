@php
    $statusBadge = [
        'sampah' => 'bg-slate-100 text-slate-600',
        'tidak_potensial' => 'bg-rose-100 text-rose-700',
        'potensial' => 'bg-amber-100 text-amber-700',
        'visit_penawaran' => 'bg-amber-100 text-amber-700',
        'deal' => 'bg-emerald-100 text-emerald-700',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Laporan Leads</h2>
                <p class="text-sm text-slate-500 mt-0.5">Pantau dan follow up calon klien</p>
            </div>

            <a href="{{ route('leads.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Tambah Leads
            </a>
        </div>
    </x-slot>

    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 mb-4 flex flex-wrap items-end gap-3">
        @if (auth()->user()->hasRole('admin'))
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Brand</label>
                <select name="brand_id" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Semua Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status</label>
            <select name="status" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Lead::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Sumber</label>
            <select name="sumber" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Semua Sumber</option>
                @foreach (\App\Models\Lead::SUMBERS as $value => $label)
                    <option value="{{ $value }}" @selected(request('sumber') == $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('leads.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="text-left px-5 py-2.5">Tanggal</th>
                        <th class="text-left px-5 py-2.5">Brand</th>
                        <th class="text-left px-5 py-2.5">Klien</th>
                        <th class="text-left px-5 py-2.5">No. WA</th>
                        <th class="text-left px-5 py-2.5">Status</th>
                        <th class="text-left px-5 py-2.5">Sumber</th>
                        <th class="text-left px-5 py-2.5">Kota</th>
                        <th class="text-left px-5 py-2.5">Paket</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-5 py-3">{{ $lead->tanggal->format('d M Y') }}</td>
                            <td class="px-5 py-3">{{ $lead->brand->name ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-navy-600 hover:underline">
                                    {{ $lead->klien }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span>{{ $lead->no_wa ?: '-' }}</span>
                                    @if ($lead->no_wa)
                                        <a href="{{ $lead->follow_up_wa_url }}" target="_blank"
                                           class="inline-flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-2 py-1 rounded-md">
                                            <x-icon name="phone" class="w-3 h-3" />
                                            Follow Up
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusBadge[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $lead->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3">{{ $lead->sumber_label }}</td>
                            <td class="px-5 py-3">{{ $lead->kota ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $lead->paket ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('leads.edit', $lead) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Edit
                                    </a>

                                    <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                                          onsubmit="return confirm('Hapus leads {{ $lead->klien }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-slate-400">Belum ada leads.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
