<x-guest-layout>

    <div class="max-w-md mx-auto bg-white shadow-lg rounded-2xl p-8">

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" class="block mt-1 w-full"
                    type="text" name="name" :value="old('name')"
                    required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="block mt-1 w-full"
                    type="email" name="email" :value="old('email')"
                    required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" class="block mt-1 w-full"
                    type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" value="Confirm Password" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                    type="password" name="password_confirmation" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-primary-button class="w-full justify-center py-3">
                    Register
                </x-primary-button>
            </div>

            <div class="text-center mt-5 text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}"
                   class="text-indigo-600 font-semibold">
                    Login
                </a>
            </div>

        </form>

    </div>

</x-guest-layout>