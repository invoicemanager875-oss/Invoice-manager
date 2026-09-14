<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Kelola Drafter</h2>
                <p class="text-sm text-slate-500 mt-0.5">Drafter mengerjakan checklist lingkup pekerjaan pada Form Order yang di-assign ke mereka.</p>
            </div>

            <a href="{{ route('drafters.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Tambah Drafter
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="text-left px-5 py-2.5">Nama</th>
                        <th class="text-left px-5 py-2.5">Email</th>
                        <th class="text-left px-5 py-2.5">Jobdesk</th>
                        <th class="text-left px-5 py-2.5">Brand</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($drafters as $drafter)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-navy-600">{{ $drafter->name }}</td>
                            <td class="px-5 py-3">{{ $drafter->email }}</td>
                            <td class="px-5 py-3">
                                @if ($drafter->jobdesk)
                                    <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full bg-navy-50 text-navy-600">{{ $drafter->jobdesk }}</span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @forelse ($drafter->brands as $brand)
                                    <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 mr-1">{{ $brand->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">Belum ada brand</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('drafters.edit', $drafter) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Edit
                                    </a>

                                    <form action="{{ route('drafters.destroy', $drafter) }}" method="POST"
                                          onsubmit="return confirm('Hapus akun drafter {{ $drafter->name }}?');">
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
                            <td colspan="5" class="text-center py-10 text-slate-400">Belum ada drafter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($drafters->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $drafters->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
