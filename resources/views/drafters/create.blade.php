<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Tambah Drafter</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form action="{{ route('drafters.store') }}" method="POST">
            @csrf

            @include('drafters._form')
        </form>
    </div>
</x-app-layout>
