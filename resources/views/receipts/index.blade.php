<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-navy-600">Kwitansi</h2>
            <p class="text-sm text-slate-500 mt-0.5">Otomatis dari invoice yang sudah lunas</p>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="text-left px-5 py-2.5">No. Kwitansi</th>
                        <th class="text-left px-5 py-2.5">Brand</th>
                        <th class="text-left px-5 py-2.5">Klien</th>
                        <th class="text-left px-5 py-2.5">Tanggal Lunas</th>
                        <th class="text-left px-5 py-2.5">Total</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="px-5 py-3">
                                <a href="{{ route('receipts.show', $invoice) }}" class="font-semibold text-navy-600 hover:underline">
                                    {{ $invoice->nomor_kwitansi }}
                                </a>
                            </td>
                            <td class="px-5 py-3">{{ $invoice->brand->name ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $invoice->klien }}</td>
                            <td class="px-5 py-3">{{ $invoice->tanggal_lunas?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3 font-medium">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('receipts.show', $invoice) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Lihat
                                    </a>
                                    <a href="{{ route('receipts.pdf', $invoice) }}" target="_blank" class="text-gold-500 hover:text-gold-600 font-medium">
                                        PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">Belum ada kwitansi.</td>
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
