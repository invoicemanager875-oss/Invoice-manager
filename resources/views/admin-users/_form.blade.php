@php
    $adminUser = $adminUser ?? null;
@endphp

<div class="grid grid-cols-1 gap-6 max-w-lg">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            value="{{ old('name', $adminUser->name ?? '') }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            value="{{ old('email', $adminUser->email ?? '') }}" required />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password" :value="$adminUser ? 'Password Baru (opsional)' : 'Password'" />
        @if ($adminUser)
            <x-password-input id="password" name="password" class="mt-1 block w-full"
                autocomplete="new-password" />
        @else
            <x-password-input id="password" name="password" class="mt-1 block w-full"
                autocomplete="new-password" required />
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
        @if ($adminUser)
            <p class="mt-1 text-xs text-slate-400">Kosongkan jika tidak ingin mengubah password.</p>
        @endif
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
        <x-password-input id="password_confirmation" name="password_confirmation" class="mt-1 block w-full"
            autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $adminUser ? 'Simpan Perubahan' : 'Tambah Admin' }}</x-primary-button>
        <a href="{{ route('admin-users.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
    </div>
</div>
