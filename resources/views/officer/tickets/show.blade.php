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

                {{-- Komentar --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-medium text-gray-700">Komentar</h3>

                    @if($ticket->replies && $ticket->replies->count())
                        <ul class="space-y-3">
                            @foreach($ticket->replies as $reply)
                                <li class="bg-white border border-gray-100 rounded-lg p-3">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $reply->author_name ?? $reply->user?->name ?? 'Staff' }}</div>
                                            <div class="text-xs text-gray-400">{{ $reply->created_at?->format('d M Y H:i') }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $reply->message }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-sm text-gray-500">Belum ada komentar untuk tiket ini.</div>
                    @endif
                </div>

                {{-- Reply form --}}
                <div>
                    <form action="{{ route('officer.tickets.reply', $ticket->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <label class="sr-only" for="message">Balasan</label>
                        <textarea name="message" id="message" rows="4" required
                            class="w-full border border-gray-200 rounded-md p-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Tulis balasan..."></textarea>

                        <div class="mt-3 flex items-center gap-3">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                                Kirim Balasan
                            </button>

                            {{-- Officer quick status change (allowed statuses can be restricted in controller) --}}
                            <form action="{{ route('officer.tickets.update_status', $ticket->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="pending" {{ (old('status', $ticket->status) == 'pending') ? 'selected' : '' }}>Pending</option>
                                        <option value="resolved" {{ (old('status', $ticket->status) == 'resolved') ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-4">
                <div class="p-4 bg-white border border-gray-100 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700">Tindakan</h4>
                    <div class="mt-3 space-y-3">
                        <a href="{{ route('officer.tickets.index') }}" class="block w-full text-center px-3 py-2 border border-gray-200 rounded-md text-sm hover:bg-gray-50">
                            Kembali
                        </a>
                    </div>
                </div>

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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const msg = document.getElementById('message');
    if (msg) msg.focus();
});
</script>
@endpush
