@props(['disabled' => false])

<div x-data="{ showPassword: false }" class="relative">
    <input :type="showPassword ? 'text' : 'password'"
        @disabled($disabled)
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-10']) }}>

    <button type="button" tabindex="-1"
        @click="showPassword = !showPassword"
        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
        <x-icon x-cloak x-show="!showPassword" name="eye" class="w-5 h-5" />
        <x-icon x-cloak x-show="showPassword" name="eye-slash" class="w-5 h-5" />
    </button>
</div>
