<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-navy-600">Task Drafter</h2>
            <p class="text-sm text-slate-500 mt-0.5">Checklist lingkup pekerjaan yang di-assign ke Anda.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-sky-50">
                <h3 class="text-sm font-semibold text-navy-600">Tugas Tersedia ({{ $available->count() }})</h3>
                <p class="text-xs text-slate-500 mt-0.5">Belum ada PIC &mdash; klik "Ambil Tugas" untuk assign ke diri Anda sendiri.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($available as $task)
                    <div class="flex items-center gap-4 px-5 py-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-800">{{ $task->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $task->formOrder->brand->name ?? '-' }} &middot;
                                {{ $task->formOrder->nama_klien }} &middot;
                                {{ $task->formOrder->nomor }}
                            </div>
                        </div>

                        <form action="{{ route('tasks.claim', $task) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white transition">
                                Ambil Tugas
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 text-sm">Tidak ada tugas tersedia saat ini.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-semibold text-navy-600">Belum Selesai ({{ $pendingByFormOrder->flatten()->count() }})</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tandai satu proyek sebagai "Fokus" untuk menunjukkan yang sedang benar-benar Anda kerjakan sekarang.</p>
            </div>

            @forelse ($pendingByFormOrder as $formOrderId => $tasks)
                @php $fo = $tasks->first()->formOrder; $isFocus = $activeFormOrderId == $formOrderId; @endphp
                <div class="border-b border-slate-100 last:border-b-0">
                    <div class="flex items-center justify-between gap-3 px-5 py-2.5 {{ $isFocus ? 'bg-indigo-50' : 'bg-white' }}">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-700 truncate">
                                {{ $fo->nama_klien }}
                                @if ($isFocus)
                                    <span class="ml-1 text-[9px] font-bold text-indigo-600 uppercase align-middle">Fokus</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $fo->brand->name ?? '-' }} &middot; {{ $fo->nomor }}
                            </div>
                        </div>

                        <form action="{{ route('tasks.toggleActiveProject', $fo) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg transition
                                    {{ $isFocus ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50' }}">
                                {{ $isFocus ? 'Nonaktifkan Fokus' : 'Aktifkan Project' }}
                            </button>
                        </form>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($tasks as $task)
                            <div class="flex items-center gap-4 px-5 py-3">
                                <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Tandai selesai"
                                        class="w-6 h-6 rounded-md border-2 border-slate-300 hover:border-emerald-500 hover:bg-emerald-50 transition"></button>
                                </form>

                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-slate-800">{{ $task->name }}</div>
                                </div>

                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 shrink-0">
                                    {{ $fo->jenis_pekerjaan ?: 'Belum ada jenis' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm">Tidak ada tugas yang belum selesai. Kerja bagus!</div>
            @endforelse
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-semibold text-navy-600">Sudah Selesai ({{ $done->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($done as $task)
                    <div class="flex items-center gap-4 px-5 py-3">
                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Buka kembali"
                                class="w-6 h-6 rounded-md bg-emerald-500 border-2 border-emerald-500 text-white flex items-center justify-center text-xs">
                                &check;
                            </button>
                        </form>

                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-slate-500 line-through">{{ $task->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ $task->formOrder->brand->name ?? '-' }} &middot;
                                {{ $task->formOrder->nama_klien }} &middot;
                                {{ $task->formOrder->nomor }}
                            </div>
                        </div>

                        <span class="text-xs text-slate-400 shrink-0">
                            {{ $task->completed_at?->translatedFormat('d M Y') }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 text-sm">Belum ada tugas yang diselesaikan.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
