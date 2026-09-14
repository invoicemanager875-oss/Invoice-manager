<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-navy-600">Dashboard</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Selamat datang, {{ auth()->user()->name }}
                    @if(!$isAdmin) &middot; {{ $brandCount }} brand Anda kelola @endif
                </p>
            </div>

            @if (\Illuminate\Support\Facades\Route::has('invoices.create'))
                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center gap-2 bg-gold-400 hover:bg-gold-500 text-navy-700 text-sm font-bold px-4 py-2 rounded-lg transition">
                    <x-icon name="plus-circle" class="w-4 h-4" />
                    Buat Invoice
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- STAT CARDS --}}
        @php
            $canOpenFormOrders = ($isAdmin || $isSuperAdmin) && \Illuminate\Support\Facades\Route::has('form-orders.index');
            $cardClass = 'bg-white rounded-xl border border-slate-200 p-4 transition';
            $clickableClass = $cardClass . ' hover:border-slate-300 hover:shadow-md cursor-pointer';
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <{{ $canOpenFormOrders ? 'a' : 'div' }}
                @if ($canOpenFormOrders) href="{{ route('form-orders.index') }}" @endif
                class="{{ $canOpenFormOrders ? $clickableClass : $cardClass }} block">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total Proyek</div>
                <div class="text-2xl font-bold text-navy-600 mt-1">{{ $stats['total_proyek'] }}</div>
                <div class="text-xs text-emerald-600 mt-1">↑ {{ $stats['sedang_berjalan'] }} aktif berjalan</div>
            </{{ $canOpenFormOrders ? 'a' : 'div' }}>

            <{{ $canOpenFormOrders ? 'a' : 'div' }}
                @if ($canOpenFormOrders) href="{{ route('form-orders.index', ['status' => 'draft']) }}" @endif
                class="{{ $canOpenFormOrders ? $clickableClass : $cardClass }} block">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Sedang Berjalan</div>
                <div class="text-2xl font-bold text-navy-600 mt-1">{{ $stats['sedang_berjalan'] }}</div>
                <div class="text-xs text-slate-400 mt-1">
                    {{ $stats['total_proyek'] ? round($stats['sedang_berjalan'] / $stats['total_proyek'] * 100) : 0 }}% dari total
                </div>
            </{{ $canOpenFormOrders ? 'a' : 'div' }}>

            <{{ $canOpenFormOrders ? 'a' : 'div' }}
                @if ($canOpenFormOrders) href="{{ route('form-orders.index', ['deadline_status' => 'mendekati']) }}" @endif
                class="{{ $canOpenFormOrders ? $clickableClass : $cardClass }} block">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Mendekati Deadline</div>
                <div class="text-2xl font-bold mt-1 {{ $stats['mendekati_deadline'] ? 'text-red-600' : 'text-navy-600' }}">{{ $stats['mendekati_deadline'] }}</div>
                <div class="text-xs mt-1 {{ $stats['mendekati_deadline'] ? 'text-red-500' : 'text-slate-400' }}">
                    {{ $stats['mendekati_deadline'] ? 'Perlu perhatian' : 'Semua aman' }}
                </div>
            </{{ $canOpenFormOrders ? 'a' : 'div' }}>

            <div class="{{ $cardClass }}">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Tugas Selesai</div>
                <div class="text-2xl font-bold text-navy-600 mt-1">{{ $stats['tugas_selesai'] }}</div>
                <div class="text-xs text-slate-400 mt-1">dari {{ $stats['total_tugas'] }} total tugas</div>
            </div>
        </div>

        @if (!\Illuminate\Support\Facades\Schema::hasTable('invoices'))
            <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3">
                💡 Modul Invoice belum dibangun &mdash; angka di atas masih placeholder (Fase 0: Auth &amp; Brand sudah aktif).
                Anda mengelola <strong>{{ $brandCount }}</strong> brand saat ini.
            </div>
        @endif

        {{-- STATUS TIM --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-navy-600">Status Tim</h3>
                <span class="text-xs text-slate-400">{{ $draftersStatus->flatten(1)->count() }} drafter</span>
            </div>

            @if ($draftersStatus->isEmpty())
                <p class="text-sm text-slate-400 text-center py-8">Belum ada drafter terdaftar.</p>
            @else
                <div class="space-y-5">
                    @foreach ($draftersStatus as $jobdesk => $drafters)
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">{{ $jobdesk }}</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($drafters as $drafter)
                                    @php
                                        $byFormOrder = $drafter->assignedTasks->groupBy('form_order_id');
                                        $hasPending = $byFormOrder->isNotEmpty();
                                        $isFocusValid = $drafter->active_form_order_id && $byFormOrder->has($drafter->active_form_order_id);
                                        $badgeClass = $isFocusValid ? 'bg-indigo-100 text-indigo-700' : ($hasPending ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500');
                                        $badgeLabel = $isFocusValid ? 'Fokus' : ($hasPending ? 'Menunggu' : 'Idle');
                                        $initials = collect(explode(' ', $drafter->name))->map(fn ($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                                    @endphp
                                    <div class="border border-slate-200 rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-9 h-9 rounded-full bg-navy-50 text-navy-600 font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $initials }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-semibold text-slate-800 truncate">{{ $drafter->name }}</div>
                                            </div>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 {{ $badgeClass }}">
                                                {{ $badgeLabel }}
                                            </span>
                                        </div>

                                        @if ($hasPending)
                                            <div class="space-y-2">
                                                @foreach ($byFormOrder as $formOrderId => $tasks)
                                                    @php $fo = $tasks->first()->formOrder; $isFocus = $isFocusValid && $formOrderId == $drafter->active_form_order_id; @endphp
                                                    <div class="rounded-md p-2 {{ $isFocus ? 'bg-indigo-50 border border-indigo-200' : 'bg-slate-50' }}">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <div class="text-xs font-semibold text-slate-700 truncate">{{ $fo->nama_klien }}</div>
                                                            @if ($isFocus)
                                                                <span class="text-[9px] font-bold text-indigo-600 uppercase shrink-0">Fokus</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[10px] text-slate-400 mb-1">{{ $fo->brand->name ?? '-' }} &middot; {{ $fo->nomor }}</div>
                                                        <ul class="space-y-0.5">
                                                            @foreach ($tasks as $task)
                                                                <li class="text-[11px] text-slate-600 flex items-center gap-1.5">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                                                    <span class="truncate">{{ $task->name }}</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-slate-400">Tidak ada tugas aktif.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
