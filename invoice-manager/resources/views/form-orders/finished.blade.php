<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Daftar Project Selesai</h2>
                <p class="text-sm text-slate-500 mt-0.5">Proyek yang sudah selesai, siap dikirim ke klien.</p>
            </div>

            <a href="{{ route('form-orders.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                &larr; Kembali ke Form Order
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
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Status Kirim</label>
            <select name="dikirim" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Semua</option>
                <option value="belum" @selected(request('dikirim') === 'belum')>Belum Dikirim</option>
                <option value="sudah" @selected(request('dikirim') === 'sudah')>Sudah Dikirim</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Filter
            </button>
            <a href="{{ route('form-orders.finished') }}" class="text-sm text-slate-500 hover:text-slate-700">Reset</a>
        </div>
    </form>

    @if ($groupedByBrand->isEmpty())
        <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-sm text-slate-400">
            Belum ada proyek yang selesai.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($groupedByBrand as $brandName => $formOrders)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-navy-600">{{ $brandName }}</h3>
                        <span class="text-xs text-slate-400">{{ $formOrders->count() }} proyek</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($formOrders as $fo)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('form-orders.show', $fo) }}" class="text-sm font-semibold text-navy-600 hover:underline">
                                        {{ $fo->nama_klien }}
                                    </a>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ $fo->nomor }} &middot; {{ $fo->tanggal_order->format('d M Y') }}
                                        @if ($fo->jenis_pekerjaan)
                                            &middot; {{ $fo->jenis_pekerjaan }}
                                        @endif
                                    </div>
                                </div>

                                @if ($fo->dikirim_at)
                                    <span class="text-xs text-emerald-600 shrink-0">
                                        Dikirim {{ $fo->dikirim_at->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 shrink-0">
                                        Belum Dikirim
                                    </span>
                                @endif

                                <form action="{{ route('form-orders.markDelivered', $fo) }}" method="POST" class="shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="text-xs font-semibold px-3 py-1.5 rounded-lg transition
                                            {{ $fo->dikirim_at ? 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                        {{ $fo->dikirim_at ? 'Batalkan' : 'Tandai Sudah Dikirim' }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
