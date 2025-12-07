<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- Admin menu --}}
                    @if(Auth::check() && (Auth::user()->role ?? '') === 'admin')
                        <x-nav-link :href="route('admin.tickets.index')" :active="request()->routeIs('admin.tickets.*')">
                            {{ __('Daftar Tiket') }}
                        </x-nav-link>

                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('Laporan') }}
                        </x-nav-link>
                    @endif

                    {{-- Profile link (role-aware) --}}
                    @php
                        $profileRoute = (Auth::check() && Auth::user()->role === 'admin')
                            ? route('admin.profile.show')
                            : ((Auth::check() && Auth::user()->role === 'officer')
                                ? route('officer.profile.show')
                                : route('profile.edit')); // fallback Breeze
                        $isActive = request()->routeIs('admin.profile.*') || request()->routeIs('officer.profile.*') || request()->routeIs('profile.*');
                    @endphp
                    <x-nav-link :href="$profileRoute" :active="$isActive">
                        {{ __('Profile') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Right side: Bell + User dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                @php
                    // Fetch notifications for current user
                    $user = Auth::user();
                    $unreadNotifs = collect();
                    $readNotifs = collect();
                    if ($user) {
                        $unreadNotifs = \Illuminate\Support\Facades\DB::table('notifications')
                            ->where('notifiable_id', $user->id)
                            ->whereNull('read_at')
                            ->orderByDesc('created_at')
                            ->limit(20)
                            ->get();
                        $readNotifs = \Illuminate\Support\Facades\DB::table('notifications')
                            ->where('notifiable_id', $user->id)
                            ->whereNotNull('read_at')
                            ->orderByDesc('created_at')
                            ->limit(20)
                            ->get();
                        $notifBadgeCount = $unreadNotifs->count();
                    } else {
                        $notifBadgeCount = 0;
                    }
                @endphp

                <!-- Bell + Panel wrapper (relative for absolute panel) -->
                <div class="relative">
                    <!-- Bell -->
                    <button id="topNotifBell" type="button"
                            class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border shadow hover:bg-gray-50"
                            title="Notifikasi" aria-haspopup="true" aria-expanded="false">
                        <svg class="w-5 h-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/>
                        </svg>
                        @if(($notifBadgeCount ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">
                                {{ $notifBadgeCount }}
                            </span>
                        @endif
                    </button>

                    <!-- Bell panel (absolute, right-aligned under bell) -->
                    <div id="topNotifPanel"
                         class="hidden absolute right-0 mt-2 w-80 bg-white border rounded-xl shadow-2xl overflow-hidden z-50">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <div class="text-sm font-semibold text-gray-800">Notifikasi</div>
                            <button type="button" id="topNotifClose" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            @foreach($unreadNotifs as $n)
                                @php
                                    $data = is_string($n->data) ? (json_decode($n->data, true) ?: []) : (is_array($n->data) ? $n->data : []);
                                    $ticketId = $data['ticket_id'] ?? null;
                                    $message  = $data['message'] ?? ($data['title'] ?? 'Notifikasi');
                                    $routeUrl = $data['url'] ?? ($ticketId
                                        ? (Auth::user()?->role === 'admin'
                                            ? route('admin.tickets.show', $ticketId)
                                            : route('officer.tickets.show', $ticketId))
                                        : '#');
                                @endphp
                                <a href="{{ $routeUrl }}"
                                   class="block px-4 py-3 bg-indigo-50 hover:bg-indigo-100 transition-colors"
                                   data-notif-id="{{ $n->id }}"
                                   data-read="false">
                                    <div class="text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                                    <div class="text-sm text-gray-800">{{ $message }}</div>
                                </a>
                            @endforeach

                            @if($readNotifs->count() > 0)
                                <div class="px-4 py-2 text-xs text-gray-400 border-t">Sebelumnya</div>
                                @foreach($readNotifs as $n)
                                    @php
                                        $data = is_string($n->data) ? (json_decode($n->data, true) ?: []) : (is_array($n->data) ? $n->data : []);
                                        $ticketId = $data['ticket_id'] ?? null;
                                        $message  = $data['message'] ?? ($data['title'] ?? 'Notifikasi');
                                        $routeUrl = $data['url'] ?? ($ticketId
                                            ? (Auth::user()?->role === 'admin'
                                                ? route('admin.tickets.show', $ticketId)
                                                : route('officer.tickets.show', $ticketId))
                                            : '#');
                                    @endphp
                                    <a href="{{ $routeUrl }}"
                                       class="block px-4 py-3 bg-white hover:bg-gray-50 transition-colors"
                                       data-notif-id="{{ $n->id }}"
                                       data-read="true">
                                        <div class="text-xs text-gray-400">{{ \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans() }}</div>
                                        <div class="text-sm text-gray-800">{{ $message }}</div>
                                    </a>
                                @endforeach
                            @else
                                @if(($unreadNotifs->count() ?? 0) === 0)
                                    <div class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @php
                            $profileRoute = (Auth::check() && Auth::user()->role === 'admin')
                                ? route('admin.profile.show')
                                : ((Auth::check() && Auth::user()->role === 'officer')
                                    ? route('officer.profile.show')
                                    : route('profile.edit'));
                        @endphp
                        <x-dropdown-link :href="$profileRoute">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            {{-- Admin responsive links --}}
            @if(Auth::check() && (Auth::user()->role ?? '') === 'admin')
                <x-responsive-nav-link :href="route('admin.tickets.index')" :active="request()->routeIs('admin.tickets.*')">
                    {{ __('Daftar Tiket') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                    {{ __('Laporan') }}
                </x-responsive-nav-link>
            @endif

            {{-- Profile responsive link (role-aware) --}}
            @php
                $profileRoute = (Auth::check() && Auth::user()->role === 'admin')
                    ? route('admin.profile.show')
                    : ((Auth::check() && Auth::user()->role === 'officer')
                        ? route('officer.profile.show')
                        : route('profile.edit'));
            @endphp
            <x-responsive-nav-link :href="$profileRoute" :active="request()->routeIs('admin.profile.*') || request()->routeIs('officer.profile.*') || request()->routeIs('profile.*')">
                {{ __('Profile') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="$profileRoute">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const bell = document.getElementById('topNotifBell');
        const panel = document.getElementById('topNotifPanel');
        const closeBtn = document.getElementById('topNotifClose');
        const badge = bell?.querySelector('span');
        const csrf = '{{ csrf_token() }}';

        function togglePanel() {
            if (!panel) return;
            const isHidden = panel.classList.contains('hidden');
            panel.classList.toggle('hidden', !isHidden ? true : false);
            // ensure panel aligns to bell and does not shift layout
            bell.setAttribute('aria-expanded', String(!isHidden));
        }
        function hidePanel() {
            panel?.classList.add('hidden');
            bell?.setAttribute('aria-expanded', 'false');
        }
        function hidePanelOnOutside(e) {
            if (!panel || panel.classList.contains('hidden')) return;
            // Only close if clicking outside the bell wrapper (relative container)
            const wrapper = bell?.parentElement;
            if (wrapper && !wrapper.contains(e.target)) hidePanel();
        }
        bell?.addEventListener('click', togglePanel);
        closeBtn?.addEventListener('click', hidePanel);
        document.addEventListener('click', hidePanelOnOutside);

        // Mark single notification read on click then navigate
        panel?.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-notif-id]');
            if (!link) return;
            const id = link.getAttribute('data-notif-id');
            const isRead = link.getAttribute('data-read') === 'true';
            if (!id) return;

            if (!isRead) {
                e.preventDefault();
                fetch('{{ route('notifications.read', ':id') }}'.replace(':id', id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
                }).then(() => {
                    if (badge) {
                        const current = parseInt(badge.textContent || '0', 10);
                        if (current > 1) badge.textContent = String(current - 1);
                        else badge.style.display = 'none';
                    }
                    link.classList.remove('bg-indigo-50');
                    link.classList.add('bg-white');
                    link.setAttribute('data-read','true');
                    window.location.href = link.href;
                }).catch(() => {
                    window.location.href = link.href;
                });
            }
        });

        // Prevent panel from going off-screen on small viewports
        function adjustPanel() {
            if (!panel) return;
            const rect = panel.getBoundingClientRect();
            const overflowRight = rect.right - window.innerWidth;
            if (overflowRight > 0) {
                panel.style.left = 'auto';
                panel.style.right = '0';
            }
        }
        window.addEventListener('resize', adjustPanel);
        adjustPanel();
    })();
    </script>
</nav>
