<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Kelola Admin</h2>
                <p class="text-sm text-slate-500 mt-0.5">Superadmin dapat melihat seluruh brand. Admin biasa hanya dapat mengelola brand & invoice yang ia buat sendiri.</p>
            </div>

            <a href="{{ route('admin-users.create') }}"
               class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                <x-icon name="plus-circle" class="w-4 h-4" />
                Tambah Admin
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
                        <th class="text-left px-5 py-2.5">Role</th>
                        <th class="text-left px-5 py-2.5">Brand Dikelola</th>
                        <th class="text-right px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($admins as $admin)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-navy-600">
                                {{ $admin->name }}
                                @if ($admin->id === auth()->id())
                                    <span class="text-xs font-normal text-slate-400">(Anda)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $admin->email }}</td>
                            <td class="px-5 py-3">
                                @if ($admin->hasRole('superadmin'))
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gold-100 text-gold-700">Superadmin</span>
                                @else
                                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">Admin</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">{{ $admin->hasRole('superadmin') ? 'Semua brand' : $admin->ownedBrands()->count() }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin-users.edit', $admin) }}" class="text-navy-500 hover:text-navy-700 font-medium">
                                        Edit
                                    </a>

                                    @if ($admin->id !== auth()->id())
                                        <form action="{{ route('admin-users.destroy', $admin) }}" method="POST"
                                              onsubmit="return confirm('Hapus akun admin {{ $admin->name }}? Brand & invoice yang pernah ia buat tidak ikut terhapus.');">
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
                            <td colspan="5" class="text-center py-10 text-slate-400">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($admins->hasPages())
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $admins->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
