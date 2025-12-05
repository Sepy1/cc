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

    // Notifikasi (24 jam terakhir)
    $seenAt = session('notif_seen_at_admin');
    $base = \App\Models\TicketEvent::with(['user','ticket'])
        ->whereIn('type', ['status_changed','replied','assigned']) // { added 'assigned' }
        ->orderBy('created_at', 'desc');

    $unreadQuery = $seenAt
        ? (clone $base)->where('created_at', '>', $seenAt)
        : (clone $base)->where('created_at', '>=', now()->subDay());
    $readQuery = $seenAt
        ? (clone $base)->where('created_at', '<=', $seenAt)->where('created_at', '>=', now()->subDay())
        : collect(); // jika belum pernah lihat, anggap tidak ada read dalam 24 jam

    $notifCount = (clone $unreadQuery)->count();
    $unread = (clone $unreadQuery)->take(20)->get();
    $read   = $readQuery instanceof \Illuminate\Support\Collection
        ? collect()
        : (clone $readQuery)->take(20)->get();
@endphp

{{-- Topbar bell --}}
<div class="fixed top-4 right-4 z-40">
    <button id="notifBellAdmin" type="button"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border shadow hover:bg-gray-50"
            aria-haspopup="true" aria-expanded="false" title="Notifikasi">
        <svg class="w-5 h-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/>
        </svg>
        @if($notifCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">{{ $notifCount }}</span>
        @endif
    </button>

    {{-- Panel daftar notif --}}
    <div id="notifPanelAdmin" class="hidden mt-2 w-80 bg-white border rounded-xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-800">Notifikasi</div>
            <button type="button" id="notifCloseAdmin" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-80 overflow-y-auto">
            {{-- Unread --}}
            @foreach($unread as $ev)
                @php
                    $isStatus = $ev->type === 'status_changed';
                    $isAssigned = $ev->type === 'assigned'; // { added }
                    $actor = $ev->user?->name ?? 'Sistem';
                    $ticketNo = $ev->ticket?->ticket_no ?? ('#'.$ev->ticket_id);
                    $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                    $label = $isStatus
                        ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? '')))
                        : ($isAssigned ? ('Tiket di-assign ke ' . ($meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')))) : 'Komentar baru');
                @endphp
                <a href="{{ route('admin.tickets.show', $ev->ticket_id) }}" class="block px-4 py-3 bg-indigo-50 hover:bg-indigo-100">
                    <div class="text-xs text-gray-500">{{ $ev->created_at?->diffForHumans() }}</div>
                    <div class="text-sm font-medium text-gray-900">{{ $label }} pada tiket {{ $ticketNo }}</div>
                    <div class="text-xs text-gray-600">oleh {{ $actor }}</div>
                    @if(!$isStatus && !empty($meta['snippet']))
                        <div class="mt-1 text-xs text-gray-700 line-clamp-2">{{ $meta['snippet'] }}</div>
                    @endif
                </a>
            @endforeach

            {{-- Read (jika ada) --}}
            @if(($read instanceof \Illuminate\Support\Collection ? $read->count() : $read->count()) > 0)
                <div class="px-4 py-2 text-xs text-gray-400 border-t">Sebelumnya</div>
                @foreach($read as $ev)
                    @php
                        $isStatus = $ev->type === 'status_changed';
                        $isAssigned = $ev->type === 'assigned'; // { added }
                        $actor = $ev->user?->name ?? 'Sistem';
                        $ticketNo = $ev->ticket?->ticket_no ?? ('#'.$ev->ticket_id);
                        $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                        $label = $isStatus
                            ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? '')))
                            : ($isAssigned ? ('Tiket di-assign ke ' . ($meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')))) : 'Komentar baru');
                    @endphp
                    <a href="{{ route('admin.tickets.show', $ev->ticket_id) }}" class="block px-4 py-3 hover:bg-gray-50">
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
    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">Daftar Tiket</h1>
            @if($notifCount > 0)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/></svg>
                    {{ $notifCount }} notif baru
                </span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex items-center gap-2">
                <label for="q" class="sr-only">Cari tiket</label>
                <input id="q" name="q" value="{{ request('q') }}" type="search"
                    placeholder="Cari nomor, judul, reporter..."
                    class="w-64 sm:w-80 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z"/></svg>
                    Cari
                </button>
            </form>

            {{-- Create Button --}}
            <a href="{{ route('admin.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Tiket
            </a>

            {{-- Edit Mode Toggle --}}
            <div class="flex items-center gap-2">
                <label for="editMode" class="text-sm text-gray-600">Edit mode</label>
                <button id="editMode" type="button"
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-300 transition focus:outline-none"
                    aria-pressed="false"
                    >
                    <span class="sr-only">Toggle edit mode</span>
                    <span aria-hidden="true" class="dot absolute left-1 w-4 h-4 bg-white rounded-full shadow transform transition"></span>
                </button>
            </div>
        </div>
    </div>

   {{-- LIST TIKET (TABLE STYLE) --}}
@if($tickets->count())

<div class="overflow-x-auto bg-white border rounded-lg shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No Tiket</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Judul</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reporter</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dibuat</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($tickets as $t)
            @php
                $type = strtolower($t->reporter_type ?? ($t->is_nasabah ? 'nasabah' : 'umum'));
                $typeBadge = $typeColors[$type] ?? $typeColors['umum'];
            @endphp
            <tr class="hover:bg-gray-50 transition">
                {{-- Ticket Number --}}
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-indigo-600 hover:underline">
                        {{ $t->ticket_no }}
                    </a>
                </td>

                {{-- Title --}}
                <td class="px-4 py-3 text-sm text-gray-800">
                    {{ \Illuminate\Support\Str::limit($t->title, 60) }}
                </td>

                {{-- Reporter --}}
                <td class="px-4 py-3 text-sm text-gray-700">
                    <div class="flex items-center gap-2">
                        <span>{{ $t->reporter_name }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded {{ $typeBadge }}">
                            {{ $type === 'nasabah' ? 'Nasabah' : 'Umum' }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-400">{{ $t->email ?? '-' }}</div>
                </td>

                {{-- Status --}}
                <td class="px-4 py-3">
                    @php
                        $statusKey = strtolower($t->status ?? 'unknown');
                        $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">
                        {{ ucfirst($t->status ?? 'Unknown') }}
                    </span>
                </td>

                {{-- Created at --}}
                <td class="px-4 py-3 text-sm text-gray-500">
                    {{ $t->created_at?->diffForHumans() ?? '-' }}
                </td>

                {{-- Actions --}}
                <td class="px-4 py-3 text-right text-sm flex items-center gap-3 justify-end">
                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-indigo-600 hover:underline">Lihat</a>

                    <a href="{{ route('admin.tickets.edit', $t->id) }}"
                       class="text-gray-600 hover:text-gray-800 small-edit">
                        Edit
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-6">
    {{ $tickets->withQueryString()->links() }}
</div>

@else
    {{-- Empty state --}}
    <div class="bg-white border rounded-lg p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M7 8h10M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada tiket</h3>
        <p class="mt-2 text-sm text-gray-500">Silakan buat tiket baru.</p>
        <div class="mt-4">
            <a href="{{ route('admin.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700">
                Buat Tiket Baru
            </a>
        </div>
    </div>
@endif
</div>

{{-- Simple JS untuk toggle edit mode --}}
@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('editMode');
        const dots = toggle.querySelector('.dot');
        const ticketsGrid = document.getElementById('ticketsGrid');
        let editMode = false;

        function setEditMode(on) {
            editMode = on;
            // toggle visual
            toggle.classList.toggle('bg-indigo-600', on);
            toggle.classList.toggle('bg-gray-300', !on);
            dots.style.transform = on ? 'translateX(20px)' : 'translateX(0)';
            toggle.setAttribute('aria-pressed', String(on));

            // show/hide edit controls
            document.querySelectorAll('.edit-actions').forEach(el => {
                el.classList.toggle('hidden', !on);
            });
            document.querySelectorAll('.small-edit').forEach(el => {
                el.classList.toggle('hidden', !on);
            });

            // add subtle highlight to cards when editing
            document.querySelectorAll('#ticketsGrid article').forEach(card => {
                card.classList.toggle('ring-2', on);
                card.classList.toggle('ring-indigo-100', on);
            });
        }

        // init
        setEditMode(false);

        toggle.addEventListener('click', () => setEditMode(!editMode));
    })();
</script>
<script>
(function () {
    const bell = document.getElementById('notifBellAdmin');
    const panel = document.getElementById('notifPanelAdmin');
    const closeBtn = document.getElementById('notifCloseAdmin');
    const badge = bell?.querySelector('span');
    const csrf = '{{ csrf_token() }}';

    function togglePanel() {
        if (!panel) return;
        panel.classList.toggle('hidden');
        // saat dibuka, tandai seen server-side dan sembunyikan badge
        if (!panel.classList.contains('hidden')) {
            fetch('{{ route('admin.notifications.seen') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(() => { if (badge) badge.style.display = 'none'; }).catch(()=>{});
        }
    }
    function hidePanelOnOutside(e) {
        if (!panel || panel.classList.contains('hidden')) return;
        if (!panel.contains(e.target) && !bell.contains(e.target)) {
            panel.classList.add('hidden');
        }
    }
    bell?.addEventListener('click', togglePanel);
    closeBtn?.addEventListener('click', () => panel.classList.add('hidden'));
    document.addEventListener('click', hidePanelOnOutside);
})();
</script>
@endpush

@endsection
