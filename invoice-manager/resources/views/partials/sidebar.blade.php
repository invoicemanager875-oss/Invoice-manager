@php
    // routeName => [label, icon, activePatterns]
    $utama = [
        ['dashboard',        'Dashboard',          'dashboard',      ['dashboard']],
        ['invoices.create',  'Buat Invoice',       'document-plus',  ['invoices.create', 'invoices.store', 'invoices.edit', 'invoices.update']],
        ['invoices.index',   'Daftar Invoice',     'document-list',  ['invoices.index', 'invoices.show', 'invoices.destroy']],
        ['receipts.index',   'Kwitansi',           'receipt',        ['receipts.*']],
        ['followups.index',  'Follow Up',          'phone',          ['followups.*']],
        ['form-orders.index','Form Order',         'clipboard',      ['form-orders.index', 'form-orders.create', 'form-orders.store', 'form-orders.show', 'form-orders.edit', 'form-orders.update', 'form-orders.destroy', 'form-orders.pdf', 'form-orders.finalize', 'form-orders.tasks.assign']],
        ['form-orders.finished', 'Project Selesai', 'archive-box',   ['form-orders.finished', 'form-orders.markDelivered']],
        ['schedules.index',  'Jadwal Perencanaan', 'calendar',       ['schedules.*']],
        ['contracts.index',  'Surat Kontrak',      'contract',       ['contracts.*']],
    ];

    if (auth()->user()->hasRole('drafter')) {
        $utama[] = ['tasks.index', 'Task Drafter', 'check-circle', ['tasks.*']];
    }

    $manajemen = [
        ['brands.index', 'Kelola Brand',    'building'],
        ['leads.index',  'Laporan Leads',   'target'],
        ['reports.index','Rekap & Grafik',  'chart-pie'],
    ];
@endphp

{{-- Sidebar: fixed off-canvas on mobile, fixed full-height on lg+ --}}
<aside
    class="fixed inset-y-0 left-0 z-50 w-64 bg-navy-600 transform transition-transform duration-200 ease-in-out
           lg:translate-x-0 lg:flex lg:flex-col"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex flex-col h-full overflow-hidden">

        {{-- Brand / logo --}}
        <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10 shrink-0">
            <div class="w-8 h-8 rounded-md bg-gold-400 flex items-center justify-center font-extrabold text-navy-700 text-sm">
                CV
            </div>
            <div class="leading-tight">
                <div class="text-white text-[13px] font-bold">
                    Basyid <span class="text-gold-400">Creative</span>
                </div>
                <div class="text-[10px] text-white/50">Architecture Management</div>
            </div>
            <button @click="sidebarOpen = false" class="ml-auto text-white/60 hover:text-white lg:hidden">
                <x-icon name="x-mark" class="w-5 h-5" />
            </button>
        </div>

        <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-6">

            {{-- UTAMA --}}
            <div>
                <p class="px-3 mb-2 text-[10px] font-bold tracking-wider text-gold-400 uppercase">Utama</p>
                <ul class="space-y-0.5">
                    @foreach ($utama as [$route, $label, $icon, $activePatterns])
                        @php $exists = \Illuminate\Support\Facades\Route::has($route); @endphp
                        <li>
                            @if ($exists)
                                <a href="{{ route($route) }}"
                                   class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition
                                          {{ request()->routeIs(...$activePatterns)
                                                ? 'bg-white/10 text-gold-400'
                                                : 'text-navy-100/80 hover:bg-white/5 hover:text-white' }}">
                                    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
                                    <span>{{ $label }}</span>
                                </a>
                            @else
                                <span class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-navy-100/30 cursor-not-allowed">
                                    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
                                    <span>{{ $label }}</span>
                                    <span class="ml-auto text-[9px] font-bold uppercase tracking-wide bg-white/5 text-navy-100/40 rounded px-1.5 py-0.5">Segera</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- MANAJEMEN --}}
            <div>
                <p class="px-3 mb-2 text-[10px] font-bold tracking-wider text-gold-400 uppercase">Manajemen</p>
                <ul class="space-y-0.5">
                    @foreach ($manajemen as [$route, $label, $icon])
                        @php $exists = \Illuminate\Support\Facades\Route::has($route); @endphp
                        <li>
                            @if ($exists)
                                <a href="{{ route($route) }}"
                                   class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition
                                          {{ request()->routeIs(explode('.', $route)[0].'.*')
                                                ? 'bg-white/10 text-gold-400'
                                                : 'text-navy-100/80 hover:bg-white/5 hover:text-white' }}">
                                    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
                                    <span>{{ $label }}</span>
                                </a>
                            @else
                                <span class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-navy-100/30 cursor-not-allowed">
                                    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
                                    <span>{{ $label }}</span>
                                    <span class="ml-auto text-[9px] font-bold uppercase tracking-wide bg-white/5 text-navy-100/40 rounded px-1.5 py-0.5">Segera</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- SUPERADMIN --}}
            @role('superadmin')
            <div>
                <p class="px-3 mb-2 text-[10px] font-bold tracking-wider text-gold-400 uppercase">Superadmin</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="{{ route('admin-users.index') }}"
                           class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition
                                  {{ request()->routeIs('admin-users.*') ? 'bg-white/10 text-gold-400' : 'text-navy-100/80 hover:bg-white/5 hover:text-white' }}">
                            <x-icon name="settings" class="w-5 h-5 shrink-0" />
                            <span>Kelola Admin</span>
                        </a>
                    </li>
                    @php $draftersExist = \Illuminate\Support\Facades\Route::has('drafters.index'); @endphp
                    <li>
                        @if ($draftersExist)
                            <a href="{{ route('drafters.index') }}"
                               class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition
                                      {{ request()->routeIs('drafters.*') ? 'bg-white/10 text-gold-400' : 'text-navy-100/80 hover:bg-white/5 hover:text-white' }}">
                                <x-icon name="user-group" class="w-5 h-5 shrink-0" />
                                <span>Kelola Drafter</span>
                            </a>
                        @else
                            <span class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-navy-100/30 cursor-not-allowed">
                                <x-icon name="user-group" class="w-5 h-5 shrink-0" />
                                <span>Kelola Drafter</span>
                                <span class="ml-auto text-[9px] font-bold uppercase tracking-wide bg-white/5 text-navy-100/40 rounded px-1.5 py-0.5">Segera</span>
                            </span>
                        @endif
                    </li>
                </ul>
            </div>
            @endrole

        </nav>

        {{-- Footer sidebar --}}
        <div class="px-5 py-4 border-t border-white/10 text-[10px] text-navy-100/40 shrink-0">
            &copy; {{ date('Y') }} Basyid Group
        </div>
    </div>
</aside>
