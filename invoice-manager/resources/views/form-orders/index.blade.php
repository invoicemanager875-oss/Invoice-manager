<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Form Order</h2>
                <p class="text-sm text-slate-500 mt-0.5">Formulir pemesanan proyek per brand</p>
            </div>

            <a href="{{ route('form-orders.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Tambah Form Order
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
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('form-orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="text-left px-5 py-2.5">Nomor</th>
                        <th class="text-left px-5 py-2.5">Brand</th>
                        <th class="text-left px-5 py-2.5">Klien</th>
                        <th class="text-left px-5 py-2.5">Tanggal</th>
                        <th class="text-left px-5 py-2.5">Deadline</th>
                        @if (config('features.drafter_tasks'))
                            <th class="text-left px-5 py-2.5">Progress</th>
                        @endif
                        <th class="text-left px-5 py-2.5">Status</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($formOrders as $fo)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ route('form-orders.show', $fo) }}" class="font-semibold text-navy-600 hover:underline">
                                    {{ $fo->nomor }}
                                </a>
                            </td>
                            <td class="px-5 py-3">{{ $fo->brand->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $fo->nama_klien }}</td>
                            <td class="px-5 py-3">{{ $fo->tanggal_order->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if (! $fo->deadline)
                                    <span class="text-slate-300">-</span>
                                @elseif ($fo->deadline_status === 'selesai')
                                    <span class="text-slate-400">{{ $fo->deadline->format('d M Y') }}</span>
                                @elseif ($fo->deadline_status === 'terlambat')
                                    <span class="text-xs font-semibold text-red-600">Terlambat {{ abs($fo->days_until_deadline) }} hari</span>
                                @elseif ($fo->deadline_status === 'mendekati')
                                    <span class="text-xs font-semibold text-amber-600">Sisa {{ $fo->days_until_deadline }} hari</span>
                                @else
                                    <span class="text-slate-500">{{ $fo->deadline->format('d M Y') }}</span>
                                @endif
                            </td>
                            @if (config('features.drafter_tasks'))
                                <td class="px-5 py-3">
                                    @if ($fo->tasks->isNotEmpty())
                                        <div class="flex items-center gap-2 w-28">
                                            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div class="h-full bg-emerald-500" style="width: {{ $fo->progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $fo->progress }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-300">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-5 py-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    {{ $fo->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $fo->status === 'selesai' ? 'Selesai' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('form-orders.show', $fo) }}" class="text-slate-500 hover:text-slate-700 font-medium">
                                        Review
                                    </a>

                                    @if (! $fo->is_locked)
                                        <a href="{{ route('form-orders.edit', $fo) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                            Edit
                                        </a>

                                        <form action="{{ route('form-orders.destroy', $fo) }}" method="POST"
                                              onsubmit="return confirm('Hapus form order {{ $fo->nomor }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ config('features.drafter_tasks') ? 8 : 7 }}" class="text-center py-10 text-slate-400">Belum ada form order.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($formOrders->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $formOrders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
