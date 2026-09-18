<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Edit Invoice — {{ $invoice->nomor }}</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-4xl">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('invoices._form')
        </form>
    </div>
</x-app-layout>
