@extends('layouts.app')
@section('content')
@php
    $statusColors = [
        'open'    => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'closed'  => 'bg-gray-100 text-gray-700',
        'resolved'=> 'bg-blue-100 text-blue-800',
        'rejected'=> 'bg-red-100 text-red-800',
    ];
    $typeColors = [
        'nasabah' => 'bg-emerald-100 text-emerald-800',
        'umum'    => 'bg-gray-100 text-gray-700',
    ];

    $seenAt = session('notif_seen_at_officer');
    $myTicketIds = \App\Models\Ticket::where('assigned_to', auth()->id())->pluck('id');

    $base = \App\Models\TicketEvent::with(['user','ticket'])
        ->whereIn('type', ['status_changed','replied','assigned'])
        ->whereIn('ticket_id', $myTicketIds)
        ->orderBy('created_at','desc');

    $unreadQuery = $seenAt
        ? (clone $base)->where('created_at', '>', $seenAt)
        : (clone $base)->where('created_at', '>=', now()->subDay());
    $readQuery = $seenAt
        ? (clone $base)->where('created_at', '<=', $seenAt)->where('created_at', '>=', now()->subDay())
        : collect();

    $notifCount = (clone $unreadQuery)->count();
    $unread = (clone $unreadQuery)->take(20)->get();
    $read   = $readQuery instanceof \Illuminate\Support\Collection ? collect() : (clone $readQuery)->take(20)->get();
@endphp

{{-- Topbar bell --}}
<div class="fixed top-4 right-4 z-40">
    <button id="notifBellOfficer" type="button"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border shadow hover:bg-gray-50"
            aria-haspopup="true" aria-expanded="false" title="Notifikasi">
        <svg class="w-5 h-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/>
        </svg>
        @if($notifCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">{{ $notifCount }}</span>
        @endif
    </button>

    <div id="notifPanelOfficer" class="hidden mt-2 w-80 bg-white border rounded-xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-800">Notifikasi</div>
            <button type="button" id="notifCloseOfficer" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @foreach($unread as $ev)
                @php
                    $isStatus = $ev->type === 'status_changed';
                    $isAssigned = $ev->type === 'assigned';
                    $actor = $ev->user?->name ?? 'Sistem';
                    $ticketNo = $ev->ticket?->ticket_no ?? ('#'.$ev->ticket_id);
                    $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                    $label = $isStatus
                        ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? '')))
                        : ($isAssigned ? ('Tiket di-assign ke ' . ($meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')))) : 'Komentar baru');
                @endphp
                <a href="{{ route('officer.tickets.show', $ev->ticket_id) }}" class="block px-4 py-3 bg-indigo-50 hover:bg-indigo-100">
                    <div class="text-xs text-gray-400">{{ $ev->created_at?->diffForHumans() }}</div>
                    <div class="text-sm text-gray-800">{{ $label }} pada tiket {{ $ticketNo }}</div>
                    <div class="text-xs text-gray-500">oleh {{ $actor }}</div>
                    @if(!$isStatus && !empty($meta['snippet']))
                        <div class="mt-1 text-xs text-gray-600 line-clamp-2">{{ $meta['snippet'] }}</div>
                    @endif
                </a>
            @endforeach

            @if(($read instanceof \Illuminate\Support\Collection ? $read->count() : $read->count()) > 0)
                <div class="px-4 py-2 text-xs text-gray-400 border-t">Sebelumnya</div>
                @foreach($read as $ev)
                    @php
                        $isStatus = $ev->type === 'status_changed';
                        $isAssigned = $ev->type === 'assigned';
                        $actor = $ev->user?->name ?? 'Sistem';
                        $ticketNo = $ev->ticket?->ticket_no ?? ('#'.$ev->ticket_id);
                        $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                        $label = $isStatus
                            ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? '')))
                            : ($isAssigned ? ('Tiket di-assign ke ' . ($meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')))) : 'Komentar baru');
                    @endphp
                    <a href="{{ route('officer.tickets.show', $ev->ticket_id) }}" class="block px-4 py-3 hover:bg-gray-50">
                        <div class="text-xs text-gray-400">{{ $ev->created_at?->diffForHumans() }}</div>
                        <div class="text-sm text-gray-800">{{ $label }} pada tiket {{ $ticketNo }}</div>
                        <div class="text-xs text-gray-500">oleh {{ $actor }}</div>
                        @if(!$isStatus && !empty($meta['snippet']))
                            <div class="mt-1 text-xs text-gray-600 line-clamp-2">{{ $meta['snippet'] }}</div>
                        @endif
                    </a>
                @endforeach
            @else
                @if($unread->isEmpty())
                    <div class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada notifikasi baru.</div>
                @endif
            @endif
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold">Tiket Saya</h1>
            @if($notifCount > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/></svg>
                    {{ $notifCount }} notif baru
                </span>
            @endif
        </div>

        <form action="{{ route('officer.tickets.index') }}" method="GET" class="flex items-center gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari tiket..." class="px-3 py-2 border rounded-md" />
            <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Cari</button>
        </form>
    </div>

    {{-- =======================
         Card grid for tickets
         ======================= --}}
    @if($tickets->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tickets as $t)
                @php
                    $type = strtolower($t->reporter_type ?? ($t->is_nasabah ? 'nasabah' : 'umum'));
                    $typeBadge = $typeColors[$type] ?? $typeColors['umum'];
                    $statusKey = strtolower($t->status ?? 'unknown');
                    $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700';
                @endphp

                <article class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-lg hover:border-gray-300 transition-all duration-300 ease-out hover:-translate-y-1">
                    <div class="px-5 py-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 pr-3">
                                <a href="{{ route('officer.tickets.show', $t->id) }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                                    {{ $t->ticket_no }}
                                </a>
                                <div class="mt-1 text-lg font-semibold text-gray-900 leading-snug">
                                    {{ \Illuminate\Support\Str::limit($t->title, 80) }}
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    {{ ucfirst($t->status ?? 'Unknown') }}
                                </span>
                                <div class="mt-2 text-xs text-gray-400">
                                    {{ $t->created_at?->diffForHumans() ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $t->reporter_name }}
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $typeBadge }}">
                                        {{ $type === 'nasabah' ? 'Nasabah' : 'Umum' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">{{ $t->email ?? '-' }}</div>
                                @if(!empty($t->detail))
                                    <div class="mt-3 text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                        {{ \Illuminate\Support\Str::limit($t->detail, 200) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-3 ml-4">
                                <a href="{{ route('officer.tickets.show', $t->id) }}" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded-lg shadow hover:bg-indigo-700 hover:shadow-md transition">
                                    Lihat
                                </a>

                                {{-- quick meta (optional) --}}
                                {{-- <div class="text-xs text-gray-400">#label</div> --}}
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $tickets->withQueryString()->links() }}
        </div>
    @else
        <div class="bg-white border rounded-lg p-8 text-center">
            <h3 class="text-lg font-medium text-gray-900">Belum ada tiket</h3>
            <p class="text-sm text-gray-500">Tidak ada tiket yang diassign ke Anda.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    const bell = document.getElementById('notifBellOfficer');
    const panel = document.getElementById('notifPanelOfficer');
    const closeBtn = document.getElementById('notifCloseOfficer');
    const badge = bell?.querySelector('span');
    const csrf = '{{ csrf_token() }}';
    let hasMarkedSeen = false;

    function togglePanel() {
        if (!panel) return;
        panel.classList.toggle('hidden');
        // Do NOT mark seen on open; keep badge/count and card colors unchanged while panel is open
    }

    function markSeen() {
        if (hasMarkedSeen) return;
        hasMarkedSeen = true;
        fetch('{{ route('officer.notifications.seen') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(() => { if (badge) badge.style.display = 'none'; }).catch(()=>{ hasMarkedSeen = false; });
    }

    function hidePanel() {
        if (!panel) return;
        if (!panel.classList.contains('hidden')) {
            panel.classList.add('hidden');
            // Mark as seen ONLY after the user closes the panel
            markSeen();
        }
    }

    function hidePanelOnOutside(e) {
        if (!panel || panel.classList.contains('hidden')) return;
        if (!panel.contains(e.target) && !bell.contains(e.target)) {
            hidePanel();
        }
    }

    bell?.addEventListener('click', togglePanel);
    closeBtn?.addEventListener('click', hidePanel);
    document.addEventListener('click', hidePanelOnOutside);
})();
</script>
@endpush
@endsection
