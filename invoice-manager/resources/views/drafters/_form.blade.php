@php
    $drafter = $drafter ?? null;
    $selectedBrandIds = $selectedBrandIds ?? [];
@endphp

<div class="grid grid-cols-1 gap-6 max-w-lg">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            value="{{ old('name', $drafter->name ?? '') }}" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            value="{{ old('email', $drafter->email ?? '') }}" required />
        <x-input-error :messages="$errors->get('email')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="password" :value="$drafter ? 'Password Baru (opsional)' : 'Password'" />
        @if ($drafter)
            <x-password-input id="password" name="password" class="mt-1 block w-full"
                autocomplete="new-password" />
        @else
            <x-password-input id="password" name="password" class="mt-1 block w-full"
                autocomplete="new-password" required />
        @endif
        <x-input-error :messages="$errors->get('password')" class="mt-1" />
        @if ($drafter)
            <p class="mt-1 text-xs text-slate-400">Kosongkan jika tidak ingin mengubah password.</p>
        @endif
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
        <x-password-input id="password_confirmation" name="password_confirmation" class="mt-1 block w-full"
            autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="jobdesk" value="Jobdesk (opsional)" />
        <select id="jobdesk" name="jobdesk"
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">- Belum ditentukan -</option>
            @foreach (\App\Models\User::JOBDESKS as $jobdesk)
                <option value="{{ $jobdesk }}" @selected(old('jobdesk', $drafter->jobdesk ?? '') === $jobdesk)>{{ $jobdesk }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('jobdesk')" class="mt-1" />
    </div>

    <div>
        <x-input-label value="Brand yang Bisa Diakses" />
        <p class="mt-1 mb-2 text-xs text-slate-400">Drafter hanya bisa di-assign sebagai PIC pada Form Order milik brand yang dipilih di sini.</p>
        <div class="space-y-2 border border-slate-200 rounded-md p-3">
            @forelse ($brands as $brand)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}"
                        {{ in_array($brand->id, old('brand_ids', $selectedBrandIds)) ? 'checked' : '' }}>
                    {{ $brand->name }}
                </label>
            @empty
                <p class="text-sm text-slate-400">Belum ada brand.</p>
            @endforelse
        </div>
        <x-input-error :messages="$errors->get('brand_ids')" class="mt-1" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $drafter ? 'Simpan Perubahan' : 'Tambah Drafter' }}</x-primary-button>
        <a href="{{ route('drafters.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
    </div>
</div>
