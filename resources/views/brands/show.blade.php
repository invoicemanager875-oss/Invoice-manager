<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">{{ $brand->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5 font-mono">{{ $brand->code }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('brands.edit', $brand) }}"
                   class="inline-flex items-center gap-2 bg-navy-600 hover:bg-navy-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                    Edit
                </a>
                <a href="{{ route('brands.index') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-navy-600 mb-4">Informasi Brand</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">NPWP</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->npwp ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Telepon</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->phone ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Email</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->email ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Website</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->website ?: '-' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Alamat</dt>
                    <dd class="mt-0.5 text-slate-700 whitespace-pre-line">{{ $brand->address ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-navy-600 mb-4">Tanda Tangan &amp; Stempel</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Nama Penandatangan</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->ttd_nama ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400 text-xs uppercase tracking-wide">Jabatan Penandatangan</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $brand->ttd_jabatan ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-navy-600 mb-4">Berkas</h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach (['logo_path' => 'Logo', 'qris_path' => 'QRIS', 'ttd_path' => 'Tanda Tangan', 'stempel_path' => 'Stempel', 'materai_path' => 'Materai'] as $column => $label)
                    <div class="text-center">
                        <div class="h-20 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
                            @if ($brand->{$column})
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($brand->{$column}) }}" alt="{{ $label }}" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-navy-600 mb-4">Tampilan</h3>
            <div class="flex items-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded border border-slate-200" style="background-color: {{ $brand->color_header ?: '#1a365d' }}"></span>
                    <span class="text-slate-500">Header</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded border border-slate-200" style="background-color: {{ $brand->color_accent ?: '#c9a227' }}"></span>
                    <span class="text-slate-500">Aksen</span>
                </div>
                @if ($brand->canva_link)
                    <a href="{{ $brand->canva_link }}" target="_blank" class="text-navy-500 hover:underline ml-auto">
                        Buka Canva &rarr;
                    </a>
                @endif
            </div>
        </div>

        <form action="{{ route('brands.destroy', $brand) }}" method="POST"
              onsubmit="return confirm('Hapus brand {{ $brand->name }}? Tindakan ini tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">
                Hapus Brand
            </button>
        </form>
    </div>
</x-app-layout>
