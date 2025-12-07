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
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold text-gray-900">Daftar Tiket</h1>
        </div>

        <div class="flex items-center gap-3">
            <form action="{{ route('admin.tickets.index') }}" method="GET" class="flex items-center gap-2">
                <label for="q" class="sr-only">Cari tiket</label>
                <input id="q" name="q" value="{{ request('q') }}" type="search"
                    placeholder="Cari nomor, judul, reporter..."
                    class="w-64 sm:w-80 px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >

                {{-- { added } Filter by status --}}
                <label for="status" class="sr-only">Status</label>
                @php
                    $statuses = ['' => 'Semua', 'open' => 'Open', 'pending' => 'Pending', 'progress' => 'Progress', 'resolved' => 'Resolved', 'closed' => 'Closed', 'rejected' => 'Rejected'];
                    $currentStatus = request('status', '');
                @endphp
                <select id="status" name="status"
                        class="px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}" @selected($currentStatus === $val)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z"/></svg>
                    Tampilkan
                </button>
            </form>

            {{-- Create Button --}}
            <a href="{{ route('admin.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Tiket
            </a>
        </div>
    </div>

   {{-- LIST TIKET (TABLE STYLE) --}}
{{-- LIST TIKET (CARD GRID) --}}
@if($tickets->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tickets as $t)
            @php
                $type = strtolower($t->reporter_type ?? ($t->is_nasabah ? 'nasabah' : 'umum'));
                $typeBadge = $typeColors[$type] ?? $typeColors['umum'];
                $statusKey = strtolower($t->status ?? 'unknown');
                $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700';
            @endphp

            <article class="
                bg-white border border-gray-200 
                rounded-2xl shadow-sm 
                hover:shadow-lg hover:border-gray-300 
                transition-all duration-300 ease-out 
                hover:-translate-y-1
            ">
                <div class="px-5 py-5">

                    {{-- Header No Tiket + Status --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('admin.tickets.show', $t->id) }}"
                                class="text-sm font-semibold text-indigo-600 hover:underline">
                                {{ $t->ticket_no }}
                            </a>

                            <div class="mt-1 text-lg font-semibold text-gray-900 leading-snug">
                                {{ \Illuminate\Support\Str::limit($t->title, 80) }}
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                {{ ucfirst($t->status) }}
                            </span>
                            <div class="mt-2 text-xs text-gray-400">
                                {{ $t->created_at?->diffForHumans() }}
                            </div>
                        </div>
                    </div>

                    {{-- Reporter --}}
                    <div class="mt-4">
                        <div class="flex items-center gap-2">
                            <div class="text-sm font-medium text-gray-800">
                                {{ $t->reporter_name }}
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $typeBadge }}">
                                {{ $type === 'nasabah' ? 'Nasabah' : 'Umum' }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $t->email ?? '-' }}
                        </div>
                    </div>

                    {{-- Detail preview --}}
                    @if(!empty($t->detail))
                        <div class="mt-4 text-sm text-gray-600 line-clamp-3 leading-relaxed">
                            {{ \Illuminate\Support\Str::limit($t->detail, 200) }}
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="mt-5 flex justify-end">
                        <a href="{{ route('admin.tickets.show', $t->id) }}"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg shadow hover:bg-indigo-700 hover:shadow-md transition">
                            Lihat
                        </a>
                    </div>

                </div>
            </article>

        @endforeach
    </div>
@else
    {{-- Empty state --}}
    <div class="bg-white border rounded-lg p-8 text-center shadow-sm">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M7 8h10M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada tiket</h3>
        <p class="mt-2 text-sm text-gray-500">Silakan buat tiket baru.</p>
        <div class="mt-4">
            <a href="{{ route('admin.tickets.create') }}"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 transition">
                Buat Tiket Baru
            </a>
        </div>
    </div>
@endif

{{-- Pagination --}}
<div class="mt-6">
    {{ $tickets->withQueryString()->links() }}
</div>
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
@endpush

@endsection
