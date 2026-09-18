<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Edit Form Order — {{ $formOrder->nomor }}</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-3xl">
        <form action="{{ route('form-orders.update', $formOrder) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('form-orders._form')
        </form>
    </div>
</x-app-layout>
