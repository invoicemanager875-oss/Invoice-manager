<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Kwitansi {{ $invoice->nomor_kwitansi }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $invoice->klien }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('receipts.pdf', $invoice) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                    Download PDF
                </a>
                <a href="{{ route('receipts.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl mx-auto">
        @include('invoices._partials.kop')

        <div class="p-8">
            <div class="text-center mb-8">
                <div class="text-2xl font-extrabold tracking-widest text-navy-600">KWITANSI</div>
                <div class="text-sm text-slate-500 mt-1">{{ $invoice->nomor_kwitansi }}</div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm mb-8">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Telah Diterima Dari</dt>
                    <dd class="mt-0.5 font-semibold text-slate-700">{{ $invoice->klien }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Tanggal Lunas</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $invoice->tanggal_lunas?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Untuk Pembayaran</dt>
                    <dd class="mt-0.5 text-slate-700">Invoice {{ $invoice->nomor }}</dd>
                </div>
            </dl>

            <div class="border-t border-slate-200 pt-4 mb-8">
                @include('invoices._partials.totals')
            </div>

            {{-- Kwitansi menandakan pembayaran sudah lunas, jadi tidak perlu lagi
                 menampilkan rekening/QRIS pembayaran — cukup tanda tangan saja. --}}
            <div>
                @include('invoices._partials.sign', ['hideQris' => true])
            </div>
        </div>
    </div>
</x-app-layout>
