<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">{{ $invoice->nomor }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $invoice->klien }}</p>
            </div>

            <div class="flex items-center gap-3">
                @if ($invoice->status !== 'lunas')
                    <form action="{{ route('invoices.lunas', $invoice) }}" method="POST"
                          onsubmit="return confirm('Tandai invoice {{ $invoice->nomor }} sebagai lunas?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                            Tandai Lunas
                        </button>
                    </form>
                @endif

                <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                    Cetak
                </a>

                @if ($invoice->status === 'menunggu')
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                        Edit
                    </a>
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST"
                          onsubmit="return confirm('Hapus invoice {{ $invoice->nomor }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                            Hapus
                        </button>
                    </form>
                @endif

                <a href="{{ route('invoices.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            @include('invoices._partials.kop')

            <div class="p-6 flex items-center justify-between flex-wrap gap-3">
                <span class="text-xs text-slate-400">Model desain: {{ $invoice->desain_tema_label }}</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                    {{ $invoice->status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        @if (! empty($invoice->sph_config['aktif'] ?? false))
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-navy-600">Surat Penawaran Harga (SPH)</div>
                @include('invoices._partials.sph')
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Klien</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $invoice->klien }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Kontak Klien</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $invoice->phone ?: '-' }} {{ $invoice->email ? '· '.$invoice->email : '' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Alamat Klien</dt>
                    <dd class="mt-0.5 text-slate-700 whitespace-pre-line">{{ $invoice->alamat ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Tanggal Invoice</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $invoice->tanggal->format('d M Y') }}</dd>
                </div>
                @if ($invoice->jatuh_tempo)
                    <div>
                        <dt class="text-slate-400 text-xs uppercase tracking-wide">Jatuh Tempo</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $invoice->jatuh_tempo->format('d M Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 text-sm font-semibold text-navy-600">Item</div>
            <div class="overflow-x-auto">
                @include('invoices._partials.items-table')
            </div>

            <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
                @include('invoices._partials.totals')
            </div>
        </div>

        @if ($invoice->terms->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                @include('invoices._partials.termin')
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                @include('invoices._partials.rekening')
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                @include('invoices._partials.sign')
            </div>
        </div>

        @if ($invoice->catatan)
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-navy-600 mb-2">Catatan</h3>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $invoice->catatan }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
