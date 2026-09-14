<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Daftar Invoice</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola invoice yang telah dibuat</p>
            </div>

            <a href="{{ route('invoices.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Buat Invoice
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
                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                <option value="lunas" @selected(request('status') === 'lunas')>Lunas</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Jatuh Tempo</label>
            <select name="jatuh_tempo" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Semua</option>
                <option value="terlambat" @selected(request('jatuh_tempo') === 'terlambat')>Siap Follow Up (Terlambat)</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('invoices.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
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
                        <th class="text-left px-5 py-2.5">Jatuh Tempo</th>
                        <th class="text-left px-5 py-2.5">Total</th>
                        <th class="text-left px-5 py-2.5">Status</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-navy-600 hover:underline">
                                    {{ $invoice->nomor }}
                                </a>
                            </td>
                            <td class="px-5 py-3">{{ $invoice->brand->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $invoice->klien }}</td>
                            <td class="px-5 py-3">{{ $invoice->tanggal->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                @if (! $invoice->jatuh_tempo)
                                    <span class="text-slate-300">-</span>
                                @elseif ($invoice->is_overdue)
                                    <span class="text-xs font-semibold text-red-600">Terlambat {{ abs($invoice->days_until_jatuh_tempo) }} hari</span>
                                @else
                                    <span class="text-slate-500">{{ $invoice->jatuh_tempo->format('d M Y') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    {{ $invoice->status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3 flex-wrap">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-slate-500 hover:text-slate-700 font-medium">
                                        Lihat
                                    </a>

                                    @if ($invoice->status !== 'lunas')
                                        <form action="{{ route('invoices.lunas', $invoice) }}" method="POST"
                                              onsubmit="return confirm('Tandai invoice {{ $invoice->nomor }} sebagai lunas?');">
                                            @csrf
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-medium">
                                                Tandai Lunas
                                            </button>
                                        </form>
                                    @endif

                                    @if ($invoice->status === 'menunggu')
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                            Edit
                                        </a>

                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                                              onsubmit="return confirm('Hapus invoice {{ $invoice->nomor }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">Belum ada invoice.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
