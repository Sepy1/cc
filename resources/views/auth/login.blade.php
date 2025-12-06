<x-guest-layout>
    {{-- Background modern gradient + decorative blobs --}}
    <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-violet-100">
        <span class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-300/20 blur-3xl"></span>
        <span class="absolute bottom-0 -right-24 w-96 h-96 rounded-full bg-violet-300/20 blur-3xl"></span>

        {{-- Center container --}}
        <div class="relative z-10 mx-auto max-w-md px-6 py-12">

            {{-- Glass card --}}
            <div class="backdrop-blur-xl bg-white/70 border border-white/40 shadow-2xl rounded-3xl p-8 transition duration-300 hover:shadow-indigo-200/50">

                {{-- Logo + title area --}}
                <div class="flex flex-col items-center gap-3 mb-6">

                    {{-- LOGO tanpa link + efek glow + floating --}}
                    <div class="relative group">

                        <img src="{{ asset('images/gg.png') }}"
                             alt="{{ config('app.name') }}"
                             class="h-48 w-auto drop-shadow-xl transition-all duration-500 ease-out group-hover:scale-105 group-hover:-translate-y-1 logo-float" />

                        {{-- soft glow circle --}}
                        <span class="absolute inset-0 m-auto w-40 h-40 rounded-full bg-indigo-300/20 blur-2xl opacity-70 group-hover:opacity-90 transition duration-500"></span>
                    </div>

                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Silahkan Login</h1>
                    </div>
                </div>

                {{-- Session Status --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition shadow-sm"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
                        <x-text-input
                            id="password"
                            class="block mt-1 w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition shadow-sm"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Remember + Forgot --}}
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me"
                                   type="checkbox"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                   name="remember">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 hover:text-indigo-700 hover:underline transition"
                               href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center justify-end">
                        <x-primary-button class="ml-3 rounded-xl px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Subtle footer note --}}
            <div class="mt-6 text-center text-xs text-gray-500">
                © {{ date('Y') }} {{ config('app.name') }} • All rights reserved
            </div>
        </div>
    </div>

    {{-- Overlay spinner --}}
    <div id="pageSpinner" class="fixed inset-0 z-[1000] flex items-center justify-center bg-white/90">
        <div class="spinner">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    {{-- Main content --}}
    <div id="pageContent" class="opacity-0 transition-opacity duration-400">
        {{-- Background modern gradient + decorative blobs --}}
        <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-violet-100">
            <span class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-indigo-300/20 blur-3xl"></span>
            <span class="absolute bottom-0 -right-24 w-96 h-96 rounded-full bg-violet-300/20 blur-3xl"></span>

            {{-- Center container --}}
            <div class="relative z-10 mx-auto max-w-md px-6 py-12">

                {{-- Glass card --}}
                <div class="backdrop-blur-xl bg-white/70 border border-white/40 shadow-2xl rounded-3xl p-8 transition duration-300 hover:shadow-indigo-200/50">

                    {{-- Logo + title area --}}
                    <div class="flex flex-col items-center gap-3 mb-6">

                        {{-- LOGO tanpa link + efek glow + floating --}}
                        <div class="relative group">

                            <img src="{{ asset('images/gg.png') }}"
                                 alt="{{ config('app.name') }}"
                                 class="h-48 w-auto drop-shadow-xl transition-all duration-500 ease-out group-hover:scale-105 group-hover:-translate-y-1 logo-float" />

                            {{-- soft glow circle --}}
                            <span class="absolute inset-0 m-auto w-40 h-40 rounded-full bg-indigo-300/20 blur-2xl opacity-70 group-hover:opacity-90 transition duration-500"></span>
                        </div>

                        <div class="text-center">
                            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Silahkan Login</h1>
                        </div>
                    </div>

                    {{-- Session Status --}}
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    {{-- Form --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <x-input-label for="email" :value="__('Email')" class="text-gray-700" />
                            <x-text-input
                                id="email"
                                class="block mt-1 w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition shadow-sm"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <x-input-label for="password" :value="__('Password')" class="text-gray-700" />
                            <x-text-input
                                id="password"
                                class="block mt-1 w-full rounded-xl border-gray-200 bg-white/70 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 transition shadow-sm"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        {{-- Remember + Forgot --}}
                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me"
                                       type="checkbox"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                       name="remember">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm text-indigo-600 hover:text-indigo-700 hover:underline transition"
                                   href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center justify-end">
                            <x-primary-button class="ml-3 rounded-xl px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- Subtle footer note --}}
                <div class="mt-6 text-center text-xs text-gray-500">
                    © {{ date('Y') }} {{ config('app.name') }} • All rights reserved
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Logo floating animation */
            @keyframes floating {
                0% { transform: translateY(0); }
                50% { transform: translateY(-6px); }
                100% { transform: translateY(0); }
            }
            .logo-float {
                animation: floating 4s ease-in-out infinite;
            }

            .spinner {
                width: 54px; height: 54px; border-radius: 50%;
                border: 4px solid #e5e7eb; border-top-color: #4f46e5;
                animation: spin 0.9s linear infinite;
                box-shadow: 0 10px 30px rgba(79,70,229,0.15);
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        </style>
    @endpush

    @push('scripts')
    <script>
        (function () {
            const spinner = document.getElementById('pageSpinner');
            const content = document.getElementById('pageContent');

            function showSpinner() { spinner && (spinner.style.display = 'flex'); content && (content.style.opacity = 0); }
            function hideSpinner() {
                if (!spinner || !content) return;
                spinner.style.opacity = 1;
                // smooth fade-out spinner then fade-in content
                spinner.style.transition = 'opacity 200ms ease';
                spinner.style.opacity = 0;
                setTimeout(() => { spinner.style.display = 'none'; content.style.opacity = 1; }, 200);
            }

            // initial: show spinner until DOM ready + small delay for smoothness
            showSpinner();
            const onReady = () => setTimeout(hideSpinner, 120);
            if (document.readyState === 'complete' || document.readyState === 'interactive') onReady();
            else document.addEventListener('DOMContentLoaded', onReady);

            // show spinner on navigation (same-tab)
            window.addEventListener('beforeunload', () => showSpinner());
            window.addEventListener('pageshow', () => hideSpinner());
        })();
    </script>
    @endpush
</x-guest-layout>
