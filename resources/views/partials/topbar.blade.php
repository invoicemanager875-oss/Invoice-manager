@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user?->name ?? '?'))
        ->map(fn ($w) => mb_substr($w, 0, 1))
        ->take(2)
        ->implode('');
    $roleLabel = $user?->hasRole('superadmin')
        ? 'Superadmin'
        : ($user?->hasRole('admin') ? 'Administrator' : 'Pengguna');
@endphp

<header class="sticky top-0 z-30 h-16 bg-navy-600 flex items-center gap-3 px-4 sm:px-6">

    {{-- Mobile hamburger --}}
    <button @click="sidebarOpen = true" class="text-white/80 hover:text-white lg:hidden">
        <x-icon name="menu" class="w-6 h-6" />
    </button>

    {{-- Page title slot (optional, set from view via $title / breadcrumb) --}}
    <div class="text-white text-sm font-semibold truncate">
        {{ $pageTitle ?? '' }}
    </div>

    <div class="ml-auto flex items-center gap-2">

        {{-- Notifications --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button @click="open = !open" class="relative p-2 rounded-full text-white/80 hover:text-white hover:bg-white/10 transition">
                <x-icon name="bell" class="w-5 h-5" />
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-gold-400"></span>
                @endif
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto bg-white rounded-xl shadow-xl ring-1 ring-black/5 z-50"
                 style="display:none">
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-sm font-semibold text-navy-600">Notifikasi</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($notifications ?? [] as $notif)
                        <div class="px-4 py-3 text-[13px] text-slate-700 hover:bg-slate-50 cursor-pointer">
                            {{ $notif['msg'] ?? '' }}
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $notif['time'] ?? '' }}</div>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-[13px] text-slate-400">
                            Tidak ada notifikasi
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Profile dropdown --}}
        <x-dropdown align="right" width="56">
            <x-slot name="trigger">
                <button class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-white/10 transition">
                    <span class="w-8 h-8 rounded-full bg-gold-400 text-navy-700 text-xs font-bold flex items-center justify-center">
                        {{ $initials ?: '?' }}
                    </span>
                    <span class="hidden sm:block text-white text-[13px] font-medium">{{ $user?->name }}</span>
                    <x-icon name="chevron-down" class="hidden sm:block w-4 h-4 text-white/60" />
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-4 py-3 border-b border-slate-100">
                    <div class="text-sm font-semibold text-navy-600">{{ $user?->name }}</div>
                    <div class="text-xs text-slate-400">{{ $roleLabel }}</div>
                </div>

                <x-dropdown-link :href="route('profile.edit')">
                    Pengaturan Profil
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Keluar
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
