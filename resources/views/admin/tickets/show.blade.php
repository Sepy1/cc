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
    $readonly = $readonly ?? false;
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-4" aria-label="Breadcrumb">
        <ol class="flex items-center gap-2">
            <li><a href="{{ route('admin.tickets.index') }}" class="hover:underline">Tiket</a></li>
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

                        {{-- Tombol Riwayat (open history modal) --}}
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

                {{-- Detail box --}}
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

   @php
    // Ambil angka saja
    $rawPhone = preg_replace('/\D/', '', $ticket->phone ?? '');

    // Ubah format: jika mulai dengan 0 → jadikan 62
    if (strlen($rawPhone) > 3 && substr($rawPhone, 0, 1) === '0') {
        $waNumber = '62' . substr($rawPhone, 1);
    } else {
        $waNumber = $rawPhone;
    }
@endphp

<div class="mt-1 flex items-center gap-2">
    <span class="text-sm text-gray-800">
        {{ $ticket->phone ?? '-' }}
    </span>

    @if($ticket->phone)
        <a href="https://wa.me/{{ $waNumber }}"
           target="_blank"
           class="text-green-600 hover:text-green-700"
           title="Chat via WhatsApp">

            <svg xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 32 32"
                 class="w-5 h-5 fill-current">
                <path d="M16.002 3.2c-7.062 0-12.8 5.738-12.8 12.8 0 2.262.593 4.469 1.721 6.414L3.2 28.8l6.595-1.689a12.744 12.744 0 0 0 6.207 1.59h.001c7.062 0 12.799-5.738 12.799-12.8s-5.737-12.8-12.8-12.8zm0 23.01h-.001a10.17 10.17 0 0 1-5.186-1.42l-.372-.221-3.916 1.002 1.045-3.826-.243-.393A10.132 10.132 0 0 1 5.6 16c0-5.75 4.652-10.4 10.402-10.4 5.75 0 10.4 4.65 10.4 10.4 0 5.75-4.65 10.4-10.4 10.4zm5.645-7.626c-.308-.154-1.82-.898-2.103-1-.282-.103-.487-.154-.692.154-.205.308-.794 1-.973 1.205-.18.205-.359.231-.667.077-.308-.154-1.302-.48-2.48-1.53-.917-.817-1.536-1.828-1.716-2.136-.18-.308-.019-.474.135-.628.139-.138.308-.359.462-.539.154-.18.205-.308.308-.513.103-.205.051-.385-.026-.539-.077-.154-.692-1.667-.948-2.288-.249-.597-.503-.516-.692-.526-.18-.01-.385-.01-.59-.01a1.14 1.14 0 0 0-.821.385c-.282.308-1.079 1.054-1.079 2.57 0 1.515 1.105 2.974 1.259 3.179.154.205 2.178 3.326 5.284 4.66.738.318 1.313.507 1.762.648.74.236 1.414.203 1.948.123.594-.088 1.82-.744 2.078-1.462.257-.718.257-1.333.18-1.462-.077-.128-.282-.205-.59-.359z"/>
            </svg>

        </a>
    @endif
</div>
</div>
                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                        <div class="text-xs text-gray-500">Terakhir diupdate</div>
                        <div class="mt-1 text-sm text-gray-800">{{ $ticket->updated_at?->diffForHumans() ?? '-' }}</div>
                    </div>
                </div>

                {{-- Komentar --}}
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
                    {{-- Form Reply (existing) --}}
                    <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
    
    <div>
        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Balasan</label>
        <textarea
            name="message"
            id="message"
            rows="4"
            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500"
            placeholder="Tulis balasan (opsional)"></textarea>
        @error('message')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-3">
        <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (opsional)</label>
        <input
            type="file"
            name="attachment"
            id="attachment"
            class="hidden"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip">

        {{-- Row: pilih lampiran (kiri) + kirim (kanan) --}}
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
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

            {{-- Submit tetap di kanan --}}
            <button type="submit"
                    class="ml-auto inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
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

            {{-- Sidebar actions --}}
            <aside class="space-y-4">
                @unless($readonly)
                <div class="p-4 bg-white border border-gray-100 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Tindakan</h4>
                    <div class="mt-3 space-y-3">
                        <a href="#" class="block w-full text-center px-3 py-2 border border-gray-200 rounded-md text-sm hover:bg-gray-50 open-edit">
                            Edit Tiket
                        </a>
                        <form action="{{ route('admin.tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Hapus tiket ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-sm px-3 py-2 border border-red-200 rounded-md text-red-600 hover:bg-red-50">
                                Hapus Tiket
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Assign --}}
                <div class="p-4 bg-white border border-gray-100 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Assign ke Officer</h4>
                    <form action="{{ route('admin.tickets.assign', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="block text-sm text-gray-700 mb-1">Assign ke petugas</label>
                        <select name="user_id" class="w-full px-3 py-2 border rounded-md" required>
                            @foreach($officers as $u)
                                <option value="{{ $u->id }}" @selected($ticket->assigned_to == $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-md">Assign</button>
                    </form>
                </div>
                @endunless

                {{-- Info kecil (tetap tampil) --}}
                <div class="p-4 bg-gray-50 border border-gray-100 rounded-lg text-sm text-gray-500">
                    <div><strong>Dibuat</strong></div>
                    <div class="mt-1 text-gray-700">{{ $ticket->created_at?->format('d M Y H:i') ?? '-' }}</div>
                    <div class="mt-3"><strong>Updated</strong></div>
                    <div class="mt-1 text-gray-700">{{ $ticket->updated_at?->format('d M Y H:i') ?? '-' }}</div>
                    <div class="mt-3"><strong>Assigned to</strong></div>
                    <div class="mt-1 text-gray-700">{{ optional($ticket->assignedTo)->name ?? ($ticket->assigned_to ? 'User #' . $ticket->assigned_to : '-') }}</div>
                </div>
            </aside>
        </div>
    </div>
</div>

{{-- EDIT MODAL (HTML inside section is okay) --}}
<div id="editModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="editModalOverlay" class="fixed inset-0 bg-black bg-opacity-40 transition-opacity"></div>

    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl transform transition-all scale-95 opacity-0"
             role="dialog" aria-modal="true" aria-labelledby="editModalTitle" id="editModalPanel">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 id="editModalTitle" class="text-lg font-semibold text-gray-900">Edit Tiket — {{ $ticket->ticket_no }}</h3>
                <button type="button" id="closeEditModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editTicketForm" action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="px-6 py-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Judul</label>
                        <input name="title" type="text" required
                            value="{{ old('title', $ticket->title) }}"
                            class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Pelapor</label>
                            <input name="reporter_name" type="text" required
                                value="{{ old('reporter_name', $ticket->reporter_name) }}"
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Kategori</label>
                            <input name="category" type="text"
                                value="{{ old('category', $ticket->category) }}"
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Email</label>
                            <input name="email" type="email"
                                value="{{ old('email', $ticket->email) }}"
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Telepon</label>
                            <input name="phone" type="text"
                                value="{{ old('phone', $ticket->phone) }}"
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700">Detail</label>
                        <textarea name="detail" rows="4"
                            class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('detail', $ticket->detail) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="pending" {{ old('status', $ticket->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="resolved" {{ old('status', $ticket->status) == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ old('status', $ticket->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                <option value="rejected" {{ old('status', $ticket->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700">Tindak Lanjut <span class="text-danger" id="tindak-required">*</span></label>
                            <textarea name="tindak_lanjut" id="tindak_lanjut" class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" rows="3" placeholder="Masukkan tindak lanjut...">{{ old('tindak_lanjut', $ticket->tindak_lanjut) }}</textarea>
                            @error('tindak_lanjut') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tambahkan media_closing (required) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Media Closing <span class="text-red-600">*</span></label>
                            <select name="media_closing" required
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @php $mc = old('media_closing', $ticket->media_closing); @endphp
                                <option value="whatsapp" @selected($mc==='whatsapp')>WhatsApp</option>
                                <option value="telephone" @selected($mc==='telephone')>Telephone</option>
                                <option value="email" @selected($mc==='email')>Email</option>
                                <option value="offline" @selected($mc==='offline')>Offline</option>
                            </select>
                            @error('media_closing') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Reporter Type (required) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Tipe Pelapor <span class="text-red-600">*</span></label>
                        @php $rt = old('reporter_type', $ticket->reporter_type ?? ($ticket->is_nasabah ? 'nasabah' : 'umum')); @endphp
                        <select name="reporter_type" required
                                class="mt-1 block w-full border border-gray-200 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="nasabah" @selected($rt==='nasabah')>Nasabah</option>
                            <option value="umum" @selected($rt==='umum')>Umum</option>
                        </select>
                        @error('reporter_type') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                        <p class="mt-1 text-xs text-gray-500">Memilih “Nasabah” akan menyetel flag is_nasabah.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="cancelEditBtn" class="px-4 py-2 rounded-md border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm hover:bg-indigo-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- HISTORY MODAL (timeline) - REPLACEMENT START --}}
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
            {{-- header (sticky) --}}
            <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white z-10">
                <h3 id="historyModalTitle" class="text-lg font-semibold text-gray-900">Riwayat Tiket — {{ $ticket->ticket_no }}</h3>
                <button type="button" id="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                    <span class="sr-only">Tutup</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- content (scrollable) --}}
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
                                            {{-- friendly rendering for status change --}}
                                            @if((isset($meta['from']) || isset($meta['to'])) && !isset($meta['changes']))
                                                @php
                                                    $from = $meta['from'] ?? '-';
                                                    $to   = $meta['to'] ?? '-';
                                                @endphp
                                                <div><strong>Status tiket diubah:</strong> dari <span class="font-medium">{{ $from }}</span> menjadi <span class="font-medium">{{ $to }}</span> <span class="text-gray-500 text-xs">oleh {{ $actor }}</span></div>
                                            @endif

                                            {{-- if meta['changes'] exists --}}
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

                                            {{-- Tampilkan tindak_lanjut jika disertakan di meta --}}
                                            @if(isset($meta['tindak_lanjut']))
                                                <div class="mt-2 text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-md p-3">
                                                    <strong>Tindak lanjut:</strong>
                                                    <div class="mt-1 whitespace-pre-line">{{ $meta['tindak_lanjut'] }}</div>
                                                </div>
                                            @endif

                                            {{-- assigned --}}
                                            @if(isset($meta['assigned_to_name']) || isset($meta['assigned_to']))
                                                <div><strong>Assigned ke:</strong> <span class="font-medium">{{ $meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')) }}</span></div>
                                            @endif

                                            {{-- reply snippet --}}
                                            @if(isset($meta['snippet']))
                                                <div><strong>Isi singkat:</strong> {{ $meta['snippet'] }}</div>
                                            @endif

                                            {{-- fallback --}}
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

            {{-- footer (sticky bottom) --}}
            <div class="px-6 py-4 border-t text-right bg-white sticky bottom-0">
                <button type="button" id="closeHistoryFooterBtn" class="px-4 py-2 rounded-md border border-gray-200 text-sm text-gray-700 hover:bg-gray-50">Tutup</button>
            </div>
        </div>
    </div>
</div>
{{-- HISTORY MODAL (timeline) - REPLACEMENT END --}}


@endsection

@push('scripts')
<script>
(function () {
    // --- Modal edit logic ---
    const openButtons = document.querySelectorAll('.open-edit');
    const modalRoot = document.getElementById('editModal');
    const modalPanel = document.getElementById('editModalPanel');
    const overlay = document.getElementById('editModalOverlay');
    const closeBtn = document.getElementById('closeEditModal');
    const cancelBtn = document.getElementById('cancelEditBtn');

    function openEditModal() {
        if (!modalRoot) return;
        modalRoot.classList.remove('hidden');
        modalPanel.style.transform = 'translateY(0) scale(1)';
        modalPanel.style.opacity = '1';
        document.body.style.overflow = 'hidden';
        const first = modalPanel.querySelector('input, textarea, select, button');
        if (first) first.focus();
        document.addEventListener('keydown', escEditHandler);
    }

    function closeEditModal() {
        if (!modalRoot) return;
        modalPanel.style.transform = 'translateY(8px) scale(.98)';
        modalPanel.style.opacity = '0';
        setTimeout(() => {
            modalRoot.classList.add('hidden');
            document.body.style.overflow = '';
        }, 180);
        document.removeEventListener('keydown', escEditHandler);
    }

    function escEditHandler(e) {
        if (e.key === 'Escape' || e.keyCode === 27) closeEditModal();
    }

    openButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openEditModal();
        });
    });

    if (overlay) overlay.addEventListener('click', closeEditModal);
    if (closeBtn) closeBtn.addEventListener('click', closeEditModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeEditModal);

    // if server returned validation errors for edit, open modal automatically
    @if($errors->any())
        openEditModal();
    @endif

    // --- History modal logic ---
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
    // reset scroll to top of content
    const content = document.getElementById('historyModalContent');
    if (content) {
        content.scrollTop = 0;
    }
    // focus the panel for accessibility
    setTimeout(() => {
        const focusable = historyPanel.querySelector('button, a, input, textarea, select');
        if (focusable) focusable.focus();
    }, 80);
    document.addEventListener('keydown', escHistoryHandler);
}

    function closeHistoryModal() {
        if (!historyRoot) return;
        historyPanel.style.transform = 'translateY(8px) scale(.98)';
        historyPanel.style.opacity = '0';
        setTimeout(() => {
            historyRoot.classList.add('hidden');
            document.body.style.overflow = '';
        }, 180);
        document.removeEventListener('keydown', escHistoryHandler);
    }

    function escHistoryHandler(e) {
        if (e.key === 'Escape' || e.keyCode === 27) closeHistoryModal();
    }

    openHistoryButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openHistoryModal();
        });
    });

    if (historyOverlay) historyOverlay.addEventListener('click', closeHistoryModal);
    if (historyCloseBtn) historyCloseBtn.addEventListener('click', closeHistoryModal);
    if (historyCloseFooterBtn) historyCloseFooterBtn.addEventListener('click', closeHistoryModal);

    // Optional: auto-focus message textarea on page load
    document.addEventListener('DOMContentLoaded', function () {
        // Jangan auto-focus agar halaman tidak auto-scroll ke bawah saat load
        const msg = document.getElementById('message'); // dipakai nanti jika perlu, tanpa focus

        // Attachment UI
        const fileInput = document.getElementById('attachment');
        const fileName  = document.getElementById('attachmentName');
        const clearBtn  = document.getElementById('clearAttachment');

        function updateName() {
            const name = fileInput && fileInput.files && fileInput.files.length ? fileInput.files[0].name : 'Belum ada file';
            if (fileName) fileName.textContent = name;
            if (clearBtn) clearBtn.classList.toggle('hidden', !(fileInput && fileInput.value));
        }

        // Komentar scroll box: auto height + auto scroll bottom
        const commentsBox  = document.getElementById('commentsScroll');
        const replySection = document.getElementById('replySection');
        function resizeCommentsBox() {
            if (!commentsBox) return;
            const top = commentsBox.getBoundingClientRect().top; // jarak dari atas viewport
            const reserve = (replySection ? replySection.offsetHeight : 0) + 24; // ruang untuk form + margin
            const h = window.innerHeight - top - reserve;
            commentsBox.style.maxHeight = (h > 220 ? h : 220) + 'px';
        }
        resizeCommentsBox();
        window.addEventListener('resize', resizeCommentsBox);
        // Auto scroll ke komentar terakhir (hanya dalam box)
        if (commentsBox) commentsBox.scrollTop = commentsBox.scrollHeight;
    });

    // Status & Tindak Lanjut toggle logic
    var statusEl = document.getElementById('status');
    var tindakField = document.getElementById('tindak_lanjut');
    var requiredBadge = document.getElementById('tindak-required');

    if (statusEl && tindakField && requiredBadge) {
        function toggleTindakRequired() {
            if (statusEl.value === 'closed') {
                tindakField.setAttribute('required', 'required');
                requiredBadge.style.display = '';
            } else {
                tindakField.removeAttribute('required');
                requiredBadge.style.display = 'none';
            }
        }

        statusEl.addEventListener('change', toggleTindakRequired);
        toggleTindakRequired();
    }
})();
</script>
@endpush
