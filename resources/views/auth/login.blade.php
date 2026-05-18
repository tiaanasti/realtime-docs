<x-guest-layout>

    <div class="w-full max-w-md mx-auto bg-white shadow-lg rounded-2xl p-8">

        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                Login
            </h1>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email')" />

                <x-text-input
                    id="email"
                    class="block mt-1 w-full"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                />

                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input
                    id="password"
                    class="block mt-1 w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        name="remember"
                    >

                    <span class="ms-2 text-sm text-gray-600">
                        Remember me
                    </span>
                </label>
            </div>

            <!-- Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center py-3 text-base">
                    Login
                </x-primary-button>
            </div>

            <!-- Register Text -->
            <div class="text-center mt-5 text-sm text-gray-600">
                Don't have an account?

                <a
                    href="{{ route('register') }}"
                    class="text-indigo-600 hover:text-indigo-800 font-semibold"
                >
                    Register
                </a>
            </div>

        </form>

    </div>

</x-guest-layout>
