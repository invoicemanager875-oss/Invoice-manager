<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Tambah Brand</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @include('brands._form')
        </form>
    </div>
</x-app-layout>
