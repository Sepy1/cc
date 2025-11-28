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
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Daftar Tiket</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola tiket bantuan — cari, filter, dan buka detail tiket.</p>
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
                    <div>{{ $t->reporter_name }}</div>
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
@endpush

@endsection
