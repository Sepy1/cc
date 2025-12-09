<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">

        <div class="max-w-md w-full bg-white shadow-lg rounded-2xl p-8 border border-gray-100">

            {{-- Title --}}
            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-2">
                Reset Password
            </h2>
            <p class="text-sm text-gray-500 text-center mb-6">
                Silakan masukkan password baru Anda.
            </p>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <!-- Hidden Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="email Anda"
                    >

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password Baru -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password Baru
                    </label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="masukkan password baru"
                    >

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Konfirmasi Password
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="ulangi password baru"
                    >

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium shadow hover:bg-indigo-700 transition">
                    Reset Password
                </button>

            </form>

            {{-- Footer --}}
            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:underline">
                    Kembali ke Login
                </a>
            </div>

        </div>

    </div>
</x-guest-layout>
