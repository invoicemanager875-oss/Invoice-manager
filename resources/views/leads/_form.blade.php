@php
    $lead = $lead ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="brand_id" value="Brand" />
        @if (auth()->user()->hasRole('admin'))
            <select id="brand_id" name="brand_id" required
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Pilih brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(old('brand_id', $lead->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="brand_id" value="{{ $brands->first()->id ?? '' }}">
            <p class="mt-1 text-sm text-slate-500">{{ $brands->first()->name ?? '-' }}</p>
        @endif
        <x-input-error :messages="$errors->get('brand_id')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="klien" value="Nama Klien" />
        <x-text-input id="klien" name="klien" type="text" class="mt-1 block w-full"
            value="{{ old('klien', $lead->klien ?? '') }}" required autofocus />
        <x-input-error :messages="$errors->get('klien')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="no_wa" value="No. WhatsApp" />
        <x-text-input id="no_wa" name="no_wa" type="text" class="mt-1 block w-full"
            value="{{ old('no_wa', $lead->no_wa ?? '') }}" placeholder="08xxxxxxxxxx" />
        <x-input-error :messages="$errors->get('no_wa')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="tanggal" value="Tanggal" />
        <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full"
            value="{{ old('tanggal', isset($lead) ? $lead->tanggal->format('Y-m-d') : now()->format('Y-m-d')) }}" required />
        <x-input-error :messages="$errors->get('tanggal')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="jam" value="Jam" />
        <x-text-input id="jam" name="jam" type="time" class="mt-1 block w-full"
            value="{{ old('jam', isset($lead) && $lead->jam ? $lead->jam->format('H:i') : '') }}" />
        <x-input-error :messages="$errors->get('jam')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (\App\Models\Lead::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $lead->status ?? 'potensial') == $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="sumber" value="Sumber" />
        <select id="sumber" name="sumber" required
            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Pilih sumber</option>
            @foreach (\App\Models\Lead::SUMBERS as $value => $label)
                <option value="{{ $value }}" @selected(old('sumber', $lead->sumber ?? '') == $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('sumber')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="kota" value="Kota" />
        <x-text-input id="kota" name="kota" type="text" class="mt-1 block w-full"
            value="{{ old('kota', $lead->kota ?? '') }}" />
        <x-input-error :messages="$errors->get('kota')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="paket" value="Jenis Paket" />
        <x-text-input id="paket" name="paket" type="text" class="mt-1 block w-full"
            value="{{ old('paket', $lead->paket ?? '') }}" placeholder="Contoh: Paket Hemat" />
        <x-input-error :messages="$errors->get('paket')" class="mt-1" />
    </div>
</div>

<div class="flex items-center gap-3 border-t border-slate-200 pt-6 mt-8">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('leads.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
</div>
