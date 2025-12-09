<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">

        <div class="max-w-md w-full bg-white shadow-lg rounded-2xl p-8 border border-gray-100">

            {{-- Title --}}
            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-2">
                Lupa Password
            </h2>
            <p class="text-sm text-gray-500 text-center mb-6">
                Masukkan email Anda. Kami akan mengirimkan link reset password.
            </p>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        value="{{ old('email') }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="masukkan email Anda"
                    >

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <button type="submit"
                    class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg shadow hover:bg-indigo-700 transition">
                    Kirim Link Reset Password
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:underline">
                    Kembali ke Login
                </a>
            </div>

        </div>

    </div>
</x-guest-layout>
