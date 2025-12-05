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

    // Reporter type (special: nasabah)
    $reporterTypeColors = [
        'nasabah' => 'bg-emerald-100 text-emerald-800',
        'umum'    => 'bg-gray-100 text-gray-700',
    ];
    $reporterType = strtolower($ticket->reporter_type ?? (($ticket->is_nasabah ?? false) ? 'nasabah' : 'umum'));
    $repBadgeClass = $reporterTypeColors[$reporterType ?: 'umum'] ?? $reporterTypeColors['umum'];

    /*
     Prepare phone for WhatsApp:
      - remove non-digits
      - change leading 0 => 62
    */
    $rawPhone = $ticket->phone ?? '';
    $onlyDigits = preg_replace('/\D/', '', $rawPhone);
    $waNumber = $onlyDigits ? preg_replace('/^0/', '62', $onlyDigits) : '';

    // Notifikasi khusus officer: berdasarkan tiket yang di-assign kepada officer
    $me = auth()->id();
    $myTicketIds = \App\Models\Ticket::where('assigned_to', $me)->pluck('id');
    $notifQuery = \App\Models\TicketEvent::with(['user','ticket'])
        ->whereIn('type', ['status_changed','replied'])
        ->where('created_at', '>=', now()->subDay())
        ->whereIn('ticket_id', $myTicketIds)
        ->orderBy('created_at','desc');
    $notifCount = (clone $notifQuery)->count();
    $notifications = (clone $notifQuery)->take(20)->get();
@endphp

{{-- Topbar bell --}}
<div class="fixed top-4 right-4 z-40">
    <button id="notifBellOfficerShow" type="button" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border shadow hover:bg-gray-50" title="Notifikasi">
        <svg class="w-5 h-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/></svg>
        @if($notifCount > 0)<span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">{{ $notifCount }}</span>@endif
    </button>
    <div id="notifPanelOfficerShow" class="hidden mt-2 w-80 bg-white border rounded-xl shadow-xl overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <div class="text-sm font-semibold text-gray-800">Notifikasi</div>
            <button type="button" id="notifCloseOfficerShow" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse($notifications as $ev)
                @php
                    $isStatus = $ev->type === 'status_changed';
                    $actor = $ev->user?->name ?? 'Sistem';
                    $ticketNo = $ev->ticket?->ticket_no ?? ('#'.$ev->ticket_id);
                    $meta = is_array($ev->meta) ? $ev->meta : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                    $label = $isStatus ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? ''))) : 'Komentar baru';
                @endphp
                <a href="{{ route('officer.tickets.show', $ev->ticket_id) }}" class="block px-4 py-3 hover:bg-gray-50">
                    <div class="text-xs text-gray-400">{{ $ev->created_at?->diffForHumans() }}</div>
                    <div class="text-sm text-gray-800">{{ $label }} pada tiket {{ $ticketNo }}</div>
                    <div class="text-xs text-gray-500">oleh {{ $actor }}</div>
                    @if(!$isStatus && !empty($meta['snippet'])) <div class="mt-1 text-xs text-gray-600 line-clamp-2">{{ $meta['snippet'] }}</div> @endif
                </a>
            @empty
                <div class="px-4 py-6 text-center text-sm text-gray-500">Tidak ada notifikasi baru.</div>
            @endforelse
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('notif'))
        @php $n = session('notif'); @endphp
        <div class="mb-4">
            <div class="inline-flex items-center px-3 py-2 rounded-md text-sm
                {{ ($n['type'] ?? '') === 'status' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2a6 6 0 00-6 6v3.586l-1.707 1.707A1 1 0 005 15h14a1 1 0 00.707-1.707L18 11.586V8a6 6 0 00-6-6zm0 20a3 3 0 003-3H9a3 3 0 003 3z"/>
                </svg>
                {{ $n['message'] ?? 'Perubahan tersimpan.' }}
            </div>
        </div>
    @endif

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
                            @if($reporterType === 'nasabah')
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $repBadgeClass }}">
                                    Nasabah
                                </span>
                            @endif
                            @if($ticket->email)
                                • <a href="mailto:{{ $ticket->email }}" class="text-indigo-600 hover:underline text-sm">{{ $ticket->email }}</a>
                            @endif
                            @if($waNumber)
                                • <a
                                    href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Halo, kami dari Support menanggapi tiket #' . ($ticket->ticket_no ?? '')) }}"
                                    target="_blank"
                                    class="text-green-600 hover:underline text-sm"
                                >WhatsApp</a>
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
                        <div class="text-xs text-gray-500">Terakhir diupdate</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $ticket->updated_at?->diffForHumans() ?? '-' }}</div>
                    </div>

                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                        <div class="text-xs text-gray-500">Tipe Pelapor</div>
                        <div class="mt-1 text-sm text-gray-800">
                            {{ $reporterType === 'nasabah' ? 'Nasabah' : 'Umum' }}
                        </div>
                    </div>
                </div>

                {{-- Informasi Nasabah (opsional) --}}
                @php
                    $hasNasabahInfo = filled($ticket->id_ktp)
                        || filled($ticket->nomor_rekening)
                        || filled($ticket->nama_ibu)
                        || filled($ticket->alamat)
                        || filled($ticket->kode_kantor)
                        || filled($ticket->media_closing);
                @endphp
                @if($hasNasabahInfo)
                    <div class="bg-white border border-gray-100 rounded-lg">
                        <div class="px-4 py-3 border-b">
                            <h3 class="text-sm font-medium text-gray-700">Informasi Nasabah</h3>
                        </div>
                        <div class="px-4 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(filled($ticket->id_ktp))
                                <div>
                                    <div class="text-xs text-gray-500">ID KTP</div>
                                    <div class="mt-1 text-sm text-gray-800">{{ $ticket->id_ktp }}</div>
                                </div>
                            @endif
                            @if(filled($ticket->nomor_rekening))
                                <div>
                                    <div class="text-xs text-gray-500">Nomor Rekening</div>
                                    <div class="mt-1 text-sm text-gray-800">{{ $ticket->nomor_rekening }}</div>
                                </div>
                            @endif
                            @if(filled($ticket->nama_ibu))
                                <div>
                                    <div class="text-xs text-gray-500">Nama Ibu</div>
                                    <div class="mt-1 text-sm text-gray-800">{{ $ticket->nama_ibu }}</div>
                                </div>
                            @endif
                            @if(filled($ticket->alamat))
                                <div class="sm:col-span-2">
                                    <div class="text-xs text-gray-500">Alamat</div>
                                    <div class="mt-1 text-sm text-gray-800 whitespace-pre-line">{{ $ticket->alamat }}</div>
                                </div>
                            @endif
                            @if(filled($ticket->kode_kantor))
                                <div>
                                    <div class="text-xs text-gray-500">Kode Kantor</div>
                                    <div class="mt-1 text-sm text-gray-800">{{ $ticket->kode_kantor }}</div>
                                </div>
                            @endif
                            @if(filled($ticket->media_closing))
                                <div>
                                    <div class="text-xs text-gray-500">Media Closing</div>
                                    <div class="mt-1 text-sm text-gray-800">
                                        {{-- normalisasi label --}}
                                        @php
                                            $mc = strtolower(trim($ticket->media_closing));
                                            $labels = ['whatsapp' => 'WhatsApp', 'telepon' => 'Telepon', 'offline' => 'Offline'];
                                            $mediaClosingLabel = $labels[$mc] ?? ucfirst($ticket->media_closing);
                                        @endphp
                                        {{ $mediaClosingLabel }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Lampiran Tambahan (opsional) --}}
                @php
                    $ktpPath = $ticket->attachment_ktp ?? null;
                    $buktiPath = $ticket->attachment_bukti ?? null;
                @endphp
                @if($ktpPath || $buktiPath)
                    <div class="bg-white border border-gray-100 rounded-lg">
                        <div class="px-4 py-3 border-b">
                            <h3 class="text-sm font-medium text-gray-700">Lampiran</h3>
                        </div>
                        <div class="px-4 py-4 space-y-3">
                            @if($ktpPath)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="text-sm text-gray-700">KTP</span>
                                    <a class="text-sm text-indigo-600 hover:underline" target="_blank" href="{{ asset('storage/' . $ktpPath) }}">
                                        {{ basename($ktpPath) }}
                                    </a>
                                </div>
                            @endif
                            @if($buktiPath)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="text-sm text-gray-700">Bukti</span>
                                    <a class="text-sm text-indigo-600 hover:underline" target="_blank" href="{{ asset('storage/' . $buktiPath) }}">
                                        {{ basename($buktiPath) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

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
        <form action="{{ route('officer.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <label class="sr-only" for="message">Balasan</label>
            <textarea name="message" id="message" rows="4"
                class="w-full border border-gray-200 rounded-md p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Tulis balasan (opsional)..."></textarea>

            {{-- Lampiran (opsional) --}}
            <div class="mt-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (opsional)</label>
                <input
                    type="file"
                    name="attachment"
                    id="attachment"
                    class="hidden"
                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip">

                {{-- Row: lampiran kiri + kirim kanan --}}
               <div class="flex items-center gap-3 flex-wrap justify-between">
                   <div class="flex items-center gap-3 min-w-0">
                        <label for="attachment"
                               class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium cursor-pointer hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Pilih Lampiran
                        </label>
                        <span id="attachmentName" class="text-sm text-gray-600 truncate max-w-[40ch]">Belum ada file</span>
                        <button type="button" id="clearAttachment" class="hidden text-xs text-gray-500 hover:text-gray-700 underline">Bersihkan</button>
                   </div>

                   <button type="submit"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                       Kirim Balasan
                   </button>
                </div>

                <p class="text-xs text-gray-500 mt-2">Max 5MB. Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP</p>
                @error('attachment')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
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

{{-- Anda bisa mengubah informasi ini di halaman Edit --}}
{{-- <a href="{{ route('officer.tickets.edit', $ticket) }}" class="text-indigo-600 text-sm underline">Edit tiket</a> --}}
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
                                // meta bisa berupa array, json string, atau null
                                $meta = is_array($ev->meta)
                                    ? $ev->meta
                                    : (is_string($ev->meta) ? (json_decode($ev->meta, true) ?: []) : []);
                            @endphp
                            <li class="mb-6 ml-6">
                                <span class="absolute -left-3 flex items-center justify-center w-6 h-6 bg-indigo-600 rounded-full ring-8 ring-white text-white text-xs">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="text-xs text-gray-400">{{ $ev->created_at?->format('d M Y H:i') }}</div>
                                <div class="mt-1">
                                    <div class="text-sm font-semibold text-gray-800">
                                        {{ $typeLabel }} <span class="text-gray-500 text-xs font-normal">oleh {{ $actor }}</span>
                                    </div>

                                    {{-- Detail meta --}}
                                    @if(!empty($meta))
                                        <div class="mt-2 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-md p-3 space-y-2">
                                            {{-- Prefer from/to jika ada --}}
                                            @if(isset($meta['from']) || isset($meta['to']))
                                                <div><strong>Status tiket diubah:</strong>
                                                    
                                                    menjadi <span class="font-medium">{{ $meta['to'] ?? '-' }}</span>
                                                </div>
                                            {{-- Jika hanya ada "status" lama, render sebagai to --}}
                                            @elseif(isset($meta['status']))
                                                <div>Status tiket diubah
                                                   menjadi <span class="font-medium">{{ $meta['status'] }}</span>
                                                </div>  
                                            @endif

                                            {{-- Changes umum --}}
                                            @if(isset($meta['changes']) && is_array($meta['changes']))
                                                @foreach($meta['changes'] as $field => $change)
                                                    @php
                                                        $fieldLabel = ucfirst(str_replace('_', ' ', $field));
                                                        $from = is_array($change) ? ($change['from'] ?? '-') : '-';
                                                        $to   = is_array($change) ? ($change['to'] ?? '-')   : (is_string($change) ? $change : '-');
                                                    @endphp
                                                    @if(strtolower($field) === 'status')
                                                        <div>Status tiket diubah
                                                            
                                                            menjadi <span class="font-medium">{{ $to }}</span>
                                                        </div>
                                                    @else
                                                        <div><strong>{{ $fieldLabel }} diubah:</strong>
                                                            <span class="font-medium">{{ $from }}</span> → <span class="font-medium">{{ $to }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif

                                            {{-- Fallback: tampilkan meta lain yang dikenal --}}
                                            @if(isset($meta['assigned_to_name']) || isset($meta['assigned_to']))
                                                <div><strong>Assigned ke:</strong> <span class="font-medium">{{ $meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')) }}</span></div>
                                            @endif
                                            @if(isset($meta['snippet']))
                                                <div><strong>Isi singkat:</strong> {{ $meta['snippet'] }}</div>
                                            @endif

                                            @php
                                                $known = isset($meta['from']) || isset($meta['to']) || isset($meta['status']) || isset($meta['changes']) || isset($meta['assigned_to_name']) || isset($meta['assigned_to']) || isset($meta['snippet']);
                                            @endphp
                                            @unless($known)
                                                <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode($meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                            @endunless
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
    // Auto-scroll komentar ke bawah (dalam box)
    function scrollCommentsBottom() {
        const box = document.getElementById('commentsScroll');
        if (box) box.scrollTop = box.scrollHeight;
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scrollCommentsBottom);
    } else {
        scrollCommentsBottom();
    }

    // Attachment UI (nama file + bersihkan)
    const fileInput = document.getElementById('attachment');
    const fileName  = document.getElementById('attachmentName');
    const clearBtn  = document.getElementById('clearAttachment');

    function updateName() {
        const name = fileInput?.files?.length ? fileInput.files[0].name : 'Belum ada file';
        if (fileName) fileName.textContent = name;
        if (clearBtn) clearBtn.classList.toggle('hidden', !(fileInput && fileInput.value));
    }
    fileInput?.addEventListener('change', updateName);
    clearBtn?.addEventListener('click', function () { if (fileInput) { fileInput.value = ''; updateName(); } });
    updateName();

    // History modal handlers (jika ada)
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

    const bell = document.getElementById('notifBellOfficerShow');
    const panel = document.getElementById('notifPanelOfficerShow');
    const closeBtn = document.getElementById('notifCloseOfficerShow');
    function togglePanel() { panel?.classList.toggle('hidden'); }
    function hidePanelOnOutside(e) {
        if (!panel || panel.classList.contains('hidden')) return;
        if (!panel.contains(e.target) && !bell.contains(e.target)) panel.classList.add('hidden');
    }
    bell?.addEventListener('click', togglePanel);
    closeBtn?.addEventListener('click', () => panel.classList.add('hidden'));
    document.addEventListener('click', hidePanelOnOutside);
})();
</script>
@endpush
