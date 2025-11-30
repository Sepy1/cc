<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite / Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        <!-- Page Heading (optional) -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    {{-- Toast container (global) --}}
    @php
        $flashSuccess = session('success');
        $flashError   = session('error');
        $flashWarning = session('warning');
    @endphp

    <div aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:items-start sm:p-6 z-50">
        <div id="toastContainer" class="w-full flex flex-col items-center space-y-4 sm:items-end">
            @if($flashSuccess)
                <div class="toast toast-success max-w-sm w-full bg-white border-l-4 border-green-500 shadow-lg rounded-lg p-4 pointer-events-auto transform translate-y-4 opacity-0">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Sukses</p>
                            <p class="text-sm text-gray-600 mt-1">{!! nl2br(e($flashSuccess)) !!}</p>
                        </div>
                        <button type="button" class="close-btn ml-3 -mr-1 p-1 text-gray-400 hover:text-gray-600">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if($flashError)
                <div class="toast toast-error max-w-sm w-full bg-white border-l-4 border-red-500 shadow-lg rounded-lg p-4 pointer-events-auto transform translate-y-4 opacity-0">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 12H6" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Error</p>
                            <p class="text-sm text-gray-600 mt-1">{!! nl2br(e($flashError)) !!}</p>
                        </div>
                        <button type="button" class="close-btn ml-3 -mr-1 p-1 text-gray-400 hover:text-gray-600">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if($flashWarning)
                <div class="toast toast-warning max-w-sm w-full bg-white border-l-4 border-yellow-400 shadow-lg rounded-lg p-4 pointer-events-auto transform translate-y-4 opacity-0">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Peringatan</p>
                            <p class="text-sm text-gray-600 mt-1">{!! nl2br(e($flashWarning)) !!}</p>
                        </div>
                        <button type="button" class="close-btn ml-3 -mr-1 p-1 text-gray-400 hover:text-gray-600">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Toast behavior script (safe / no PHP inside JS) --}}
    <script>
        (function () {
            function hideToast(toast) {
                toast.style.transform = 'translateY(12px)';
                toast.style.opacity = '0';
                setTimeout(() => {
                    if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                }, 320);
            }

            document.addEventListener('DOMContentLoaded', function () {
                const toasts = document.querySelectorAll('.toast');
                toasts.forEach((toast, idx) => {
                    // show with stagger
                    setTimeout(() => {
                        toast.style.transform = 'translateY(0)';
                        toast.style.opacity = '1';
                    }, 50 + idx * 120);

                    // auto hide after 4.5s (+ small stagger)
                    const autoHide = setTimeout(() => hideToast(toast), 4500 + idx * 200);

                    // close button
                    const btn = toast.querySelector('.close-btn');
                    if (btn) {
                        btn.addEventListener('click', function (e) {
                            e.preventDefault();
                            clearTimeout(autoHide);
                            hideToast(toast);
                        });
                    }
                });
            });
        })();
    </script>

    {{-- Allow child views to push scripts (Chart.js and other page scripts) --}}
    @stack('scripts')
</body>
</html>
