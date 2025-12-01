@extends('layouts.app')

@section('content')
@php
    $statusColors = [
        'open'     => 'bg-green-100 text-green-800',
        'pending'  => 'bg-yellow-100 text-yellow-800',
        'closed'   => 'bg-gray-100 text-gray-700',
        'resolved' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
    ];

/*
 Prepare phone for WhatsApp:
  - remove non-digits
  - change leading 0 => 62
*/
$rawPhone = $ticket->phone ?? '';
$onlyDigits = preg_replace('/\D/', '', $rawPhone);
$waNumber = $onlyDigits ? preg_replace('/^0/', '62', $onlyDigits) : '';
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <li><a href="{{ route('officer.tickets.index') }}" class="hover:underline">Tiket Saya</a></li>
            <li>/</li>
            <li class="text-gray-700 font-medium">#{{ $ticket->ticket_no }}</li>
        </ol>
    </nav>

    {{-- Card container --}}
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
            {{-- Main content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                            {{ $ticket->ticket_no }} — {{ $ticket->title }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Dilaporkan oleh <span class="font-medium text-gray-700">{{ $ticket->reporter_name }}</span>
                            @if($ticket->email)
                                • <a href="mailto:{{ $ticket->email }}" class="text-indigo-600 hover:underline text-sm">{{ $ticket->email }}</a>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        @php
                            $statusKey = strtolower($ticket->status ?? 'unknown');
                            $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $badgeClass }}">
                            {{ ucfirst($ticket->status ?? 'Unknown') }}
                        </span>

                        <div class="text-right text-xs text-gray-400">
                            <div>Dibuat: {{ $ticket->created_at?->format('d M Y H:i') ?? '-' }}</div>
                            <div class="mt-1">ID: <span class="text-gray-600">{{ $ticket->id }}</span></div>
                        </div>

                        <a href="#" class="open-history inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                           title="Lihat riwayat tiket">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Riwayat
                        </a>
                    </div>
                </div>

                {{-- Detail --}}
                <div class="bg-gray-50 border border-gray-100 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Detail masalah</h3>
                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $ticket->detail ?? '-' }}</p>
                </div>

                {{-- Metadata row --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                        <div class="text-xs text-gray-500">Kategori</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $ticket->category ?? '-' }}</div>
                    </div>
                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                        <div class="text-xs text-gray-500">Kontak</div>
                        <div class="mt-1 flex items-center gap-3">
                            <span class="text-sm text-gray-800">{{ $ticket->phone ?? '-' }}</span>

                            @if($waNumber)
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank"
                                   class="inline-flex items-center px-2 py-1 border rounded text-sm text-green-700 hover:bg-green-50"
                                   title="Chat via WhatsApp">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-4 h-4 fill-current mr-1">
                                        <path d="M16.002 3.2c-7.062 0-12.8 5.738-12.8 12.8 0 2.262.593 4.469 1.721 6.414L3.2 28.8l6.595-1.689a12.744 12.744 0 0 0 6.207 1.59h.001c7.062 0 12.799-5.738 12.799-12.8s-5.737-12.8-12.8-12.8zm0 23.01h-.001a10.17 10.17 0 0 1-5.186-1.42l-.372-.221-3.916 1.002 1.045-3.826-.243-.393A10.132 10.132 0 0 1 5.6 16c0-5.75 4.652-10.4 10.402-10.4 5.75 0 10.4 4.65 10.4 10.4 0 5.75-4.65 10.4-10.4 10.4zm5.645-7.626c-.308-.154-1.82-.898-2.103-1-.282-.103-.487-.154-.692.154-.205.308-.794 1-.973 1.205-.18.205-.359.231-.667.077-.308-.154-1.302-.48-2.48-1.53-.917-.817-1.536-1.828-1.716-2.136-.18-.308-.019-.474.135-.628.139-.138.308-.359.462-.539.154-.18.205-.308.308-.513.103-.205.051-.385-.026-.539-.077-.154-.692-1.667-.948-2.288-.249-.597-.503-.516-.692-.526-.18-.01-.385-.01-.59-.01a1.14 1.14 0 0 0-.821.385c-.282.308-1.079 1.054-1.079 2.57 0 1.515 1.105 2.974 1.259 3.179.154.205 2.178 3.326 5.284 4.66.738.318 1.313.507 1.762.648.74.236 1.414.203 1.948.123.594-.088 1.82-.744 2.078-1.462.257-.718.257-1.333.18-1.462-.077-.128-.282-.205-.59-.359z"/>
                                    </svg>
                                    WA
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                        <div class="text-xs text-gray-500">Terakhir diupdate</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $ticket->updated_at?->diffForHumans() ?? '-' }}</div>
                    </div>
                </div>

                {{-- Komentar: card + scrollable --}}
                <div class="bg-white border border-gray-100 rounded-lg">
                    <div class="px-4 py-3 border-b">
                        <h3 class="text-sm font-medium text-gray-700">Komentar</h3>
                    </div>
                    <div id="commentsScroll" class="px-4 py-4 space-y-4 overflow-y-auto" style="max-height: 48vh;">
                        @forelse ($ticket->replies ?? [] as $reply)
                            <div class="bg-gray-50 border border-gray-100 rounded-lg p-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800">{{ $reply->author_name ?? $reply->user?->name ?? 'Staff' }}</div>
                                        <div class="text-xs text-gray-400">{{ $reply->created_at?->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                                @if(filled($reply->message))
                                    <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reply->message }}</div>
                                @endif
                                @if(!empty($reply->attachment))
                                    <div class="mt-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <a class="text-sm text-indigo-600 hover:underline" target="_blank"
                                           href="{{ asset('storage/' . $reply->attachment) }}">
                                           {{ basename($reply->attachment) }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Belum ada aktivitas atau balasan untuk tiket ini.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Reply form --}}
                <div id="replySection">
                    <form action="{{ route('officer.tickets.reply', $ticket->id) }}" method="POST" class="space-y-3">
                         @csrf
                         <label class="sr-only" for="message">Balasan</label>
                         <textarea name="message" id="message" rows="4" required
                             class="w-full border border-gray-200 rounded-md p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                             placeholder="Tulis balasan..."></textarea>

                        <div class="mt-3 flex items-center gap-3 flex-wrap">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                                Kirim Balasan
                            </button>
                        </div>
                     </form>
                 </div>
            </div>

            {{-- Sidebar --}}
             <aside class="space-y-4">
                {{-- Info kecil --}}
                <div class="p-4 bg-gray-50 border border-gray-100 rounded-lg text-sm text-gray-500">
                    <div><strong>Dibuat</strong></div>
                    <div class="mt-1 text-gray-700">{{ $ticket->created_at?->format('d M Y H:i') ?? '-' }}</div>
                    <div class="mt-3"><strong>Updated</strong></div>
                    <div class="mt-1 text-gray-700">{{ $ticket->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                    <div class="mt-3"><strong>Assigned to</strong></div>
                    <div class="mt-1 text-gray-700">{{ optional($ticket->assignedTo)->name ?? ($ticket->assigned_to ? 'User #' . $ticket->assigned_to : '-') }}</div>
                </div>

                {{-- Card Aksi Tiket: Riwayat + Update Status --}}
                <div class="p-4 bg-white border border-gray-100 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700">Perbarui Status</h4>
        <form action="{{ route('officer.tickets.update_status', $ticket->id) }}" method="POST" class="mt-3">
            @csrf
            <label for="status" class="block text-sm text-gray-600 mb-1">Status</label>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <select name="status" id="status"
                            class="pl-3 pr-9 py-2 border border-gray-200 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 appearance-none">
                        <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </div>
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                    Simpan
                </button>
            </div>
            @error('status') <div class="text-xs text-red-600 mt-2">{{ $message }}</div> @enderror
        </form>
    </div>
             </aside>
        </div>
    </div>
</div>
@endsection

{{-- HISTORY MODAL (timeline) --}}
<div id="historyModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="historyModalOverlay" class="fixed inset-0 bg-black bg-opacity-40 transition-opacity"></div>

    <div class="fixed inset-0 flex items-start justify-center p-6">
        <div
            id="historyModalPanel"
            class="max-w-3xl w-full bg-white rounded-2xl shadow-xl transform transition-all scale-95 opacity-0 flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-labelledby="historyModalTitle"
            style="height: calc(100vh - 96px);"
        >
            <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 id="historyModalTitle" class="text-lg font-semibold text-gray-900">Riwayat Tiket — {{ $ticket->ticket_no ?? '' }}</h3>
                <button type="button" id="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto" id="historyModalContent" style="max-height: calc(100vh - 220px);">
                @if(isset($ticket->events) && $ticket->events->count())
                    <ol class="relative border-l border-gray-200 ml-2">
                        @foreach($ticket->events->sortBy('created_at') as $ev)
                            @php
                                $typeLabel = ucfirst(str_replace('_', ' ', $ev->type));
                                $actor = $ev->user?->name ?? 'Sistem';
                                $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? json_decode($ev->meta, true) : []);
                            @endphp
                            <li class="mb-6 ml-6">
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 bg-indigo-600 rounded-full ring-8 ring-white text-white text-xs">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="text-xs text-gray-400">{{ $ev->created_at?->format('d M Y H:i') }}</div>
                                <div class="mt-1">
                                    <div class="text-sm font-semibold text-gray-800">{{ $typeLabel }} <span class="text-gray-500 text-xs font-normal">oleh {{ $actor }}</span></div>

                                    @if(!empty($meta) && is_array($meta))
                                        <div class="mt-2 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-md p-3 space-y-1">
                                            @if((isset($meta['from']) || isset($meta['to'])) && !isset($meta['changes']))
                                                @php $from = $meta['from'] ?? '-'; $to = $meta['to'] ?? '-'; @endphp
                                                <div><strong>Status tiket diubah:</strong> dari <span class="font-medium">{{ $from }}</span> menjadi <span class="font-medium">{{ $to }}</span> <span class="text-gray-500 text-xs">oleh {{ $actor }}</span></div>
                                            @endif

                                            @if(isset($meta['changes']) && is_array($meta['changes']))
                                                @foreach($meta['changes'] as $field => $change)
                                                    @php
                                                        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                                                        $from = $change['from'] ?? '-';
                                                        $to   = $change['to'] ?? '-';
                                                    @endphp
                                                    @if(strtolower($field) === 'status')
                                                        <div><strong>Status tiket diubah:</strong> dari <span class="font-medium">{{ $from }}</span> menjadi <span class="font-medium">{{ $to }}</span> <span class="text-gray-500 text-xs">oleh {{ $actor }}</span></div>
                                                    @else
                                                        <div><strong>{{ $fieldLabel }} diubah:</strong> <span class="font-medium">{{ $from }}</span> → <span class="font-medium">{{ $to }}</span></div>
                                                    @endif
                                                @endforeach
                                            @endif

                                            @if(isset($meta['tindak_lanjut']))
                                                <div class="mt-2 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-md p-3">
                                                    <strong>Tindak lanjut:</strong>
                                                    <div class="mt-1 whitespace-pre-line">{{ $meta['tindak_lanjut'] }}</div>
                                                </div>
                                            @endif

                                            @if(isset($meta['assigned_to_name']) || isset($meta['assigned_to']))
                                                <div><strong>Assigned ke:</strong> <span class="font-medium">{{ $meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')) }}</span></div>
                                            @endif

                                            @if(isset($meta['snippet']))
                                                <div><strong>Isi singkat:</strong> {{ $meta['snippet'] }}</div>
                                            @endif

                                            @php
                                                $known = isset($meta['assigned_to_name']) || isset($meta['assigned_to']) || isset($meta['from']) || isset($meta['to']) || isset($meta['changes']) || isset($meta['snippet']) || isset($meta['ticket_no']) || isset($meta['title']);
                                            @endphp
                                            @if(! $known)
                                                <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="text-sm text-gray-500">Belum ada riwayat untuk tiket ini.</div>
                @endif
            </div>

            <div class="px-6 py-4 border-t text-right bg-white sticky bottom-0">
                <button type="button" id="closeHistoryFooterBtn" class="px-4 py-2 rounded-md border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    // Komentar: auto-scroll ke paling bawah dalam box saja
    function scrollCommentsBottom() {
        const box = document.getElementById('commentsScroll');
        if (box) box.scrollTop = box.scrollHeight;
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollCommentsBottom);
    } else {
        scrollCommentsBottom();
    }

    // History modal (open/close) jika belum ada
    const openHistoryButtons = document.querySelectorAll('.open-history');
    const historyRoot = document.getElementById('historyModal');
    const historyPanel = document.getElementById('historyModalPanel');
    const historyOverlay = document.getElementById('historyModalOverlay');
    const historyCloseBtn = document.getElementById('closeHistoryModal');
    const historyCloseFooterBtn = document.getElementById('closeHistoryFooterBtn');

    function openHistoryModal() {
        if (!historyRoot) return;
        historyRoot.classList.remove('hidden');
        historyPanel.style.transform = 'translateY(0) scale(1)';
        historyPanel.style.opacity = '1';
        document.body.style.overflow = 'hidden';
        const content = document.getElementById('historyModalContent');
        if (content) content.scrollTop = 0;
        document.addEventListener('keydown', escHistoryHandler);
    }
    function closeHistoryModal() {
        if (!historyRoot) return;
        historyPanel.style.transform = 'translateY(8px) scale(.98)';
        historyPanel.style.opacity = '0';
        setTimeout(() => {
            historyRoot.classList.add('hidden');
            document.body.style.overflow = '';
        }, 160);
        document.removeEventListener('keydown', escHistoryHandler);
    }
    function escHistoryHandler(e) { if (e.key === 'Escape' || e.keyCode === 27) closeHistoryModal(); }

    openHistoryButtons.forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); openHistoryModal(); }));
    historyOverlay?.addEventListener('click', closeHistoryModal);
    historyCloseBtn?.addEventListener('click', closeHistoryModal);
    historyCloseFooterBtn?.addEventListener('click', closeHistoryModal);
})();
</script>
@endpush
