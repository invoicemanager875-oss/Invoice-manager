<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Daftar Brand</h2>
                <p class="text-sm text-slate-500 mt-0.5">Kelola brand yang Anda miliki</p>
            </div>

            <a href="{{ route('brands.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Tambah Brand
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="text-left px-5 py-2.5">Kode</th>
                        <th class="text-left px-5 py-2.5">Nama</th>
                        <th class="text-left px-5 py-2.5">Email</th>
                        <th class="text-left px-5 py-2.5">Telepon</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brands as $brand)
                        <tr>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $brand->code }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('brands.show', $brand) }}" class="font-semibold text-navy-600 hover:underline">
                                    {{ $brand->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3">{{ $brand->email ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $brand->phone ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('brands.edit', $brand) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Edit
                                    </a>

                                    <form action="{{ route('brands.destroy', $brand) }}" method="POST"
                                          onsubmit="return confirm('Hapus brand {{ $brand->name }}?');">
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
                            <td colspan="5" class="text-center py-10 text-slate-400">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($brands->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $brands->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
