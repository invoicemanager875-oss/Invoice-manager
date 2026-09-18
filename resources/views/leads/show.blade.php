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
                <h2 class="text-xl font-bold text-navy-600">{{ $lead->klien }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $lead->brand->name ?? '-' }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('leads.edit', $lead) }}"
                   class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                    Edit
                </a>
                <a href="{{ route('leads.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-navy-600">Informasi Leads</h3>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusBadge[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">
                    {{ $lead->status_label }}
                </span>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Tanggal</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $lead->tanggal->format('d M Y') }} {{ $lead->jam ? '· '.$lead->jam->format('H:i') : '' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">No. WhatsApp</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $lead->no_wa ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Sumber</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $lead->sumber_label }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Kota</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $lead->kota ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Jenis Paket</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $lead->paket ?: '-' }}</dd>
                </div>
                @if ($lead->creator)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-400 text-xs uppercase tracking-wide">Dicatat oleh</dt>
                        <dd class="mt-0.5 text-slate-700">{{ $lead->creator->name }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($lead->no_wa)
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-navy-600 mb-4">Follow Up</h3>
                <a href="{{ $lead->follow_up_wa_url }}" target="_blank"
                   class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                    <x-icon name="phone" class="w-4 h-4" />
                    Follow Up via WhatsApp
                </a>
            </div>
        @endif

        <form action="{{ route('leads.destroy', $lead) }}" method="POST"
              onsubmit="return confirm('Hapus leads {{ $lead->klien }}? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                Hapus Leads
            </button>
        </form>
    </div>
</x-app-layout>
