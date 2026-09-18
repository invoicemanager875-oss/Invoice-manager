<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full focus:border-navy-600 focus:ring-navy-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-password-input id="password" class="block mt-1 w-full focus:border-navy-600 focus:ring-navy-500"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-navy-600 shadow-sm focus:ring-navy-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-navy-600 hover:text-navy-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3 !bg-navy-600 hover:!bg-navy-700 focus:!ring-navy-500">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-navy-600 hover:text-navy-800 font-semibold">Daftar</a>
        </p>
    </form>
</x-guest-layout>
