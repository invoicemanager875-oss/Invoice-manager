<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Tambah Leads</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf

            @include('leads._form')
        </form>
    </div>
</x-app-layout>
