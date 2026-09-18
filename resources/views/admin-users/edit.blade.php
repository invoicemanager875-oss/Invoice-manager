<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-navy-600">Edit Admin</h2>
    </x-slot>

    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form action="{{ route('admin-users.update', $adminUser) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin-users._form')
        </form>
    </div>
</x-app-layout>
