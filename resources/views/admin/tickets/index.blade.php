@extends('layouts.app')

@section('content')
@php
    $statusColors = [
        'open'     => 'bg-green-50 text-green-700 ring-1 ring-green-200',
        'pending'  => 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
        'closed'   => 'bg-gray-100 text-gray-700 ring-1 ring-gray-300',
        'resolved' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',
        'rejected' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
    ];
    $currentStatus = request('status');
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Daftar Tiket</h1>
        <p class="mt-1 text-sm text-gray-500">Kelola tiket bantuan — cari, filter, dan buka detail tiket.</p>
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('admin.tickets.index') }}"
          class="flex flex-col gap-3 md:flex-row md:items-center md:gap-4 mb-5">

        <div class="flex items-center gap-2">
            <div class="relative">
                <input
                    name="q"
                    type="search"
                    value="{{ request('q') }}"
                    placeholder="Cari nomor, judul, reporter..."
                    class="w-72 md:w-80 px-3 py-2 border rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                >
                @if(request('q'))
                    <button type="button"
                        onclick="document.querySelector('input[name=q]').value=''; this.closest('form').submit();"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                @endif
            </div>

            <select
                name="status"
                class="px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach(['open','pending','resolved','closed','rejected'] as $st)
                    <option value="{{ $st }}" @selected($currentStatus===$st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md shadow-sm text-sm hover:bg-indigo-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z"/>
                </svg>
                Cari
            </button>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Tiket
            </a>

            <div class="flex items-center gap-2">
                <label for="editMode" class="text-sm text-gray-600">Edit mode</label>
                <button id="editMode" type="button"
                    class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-300 transition focus:outline-none"
                    aria-pressed="false">
                    <span class="sr-only">Toggle edit mode</span>
                    <span aria-hidden="true"
                          class="dot absolute left-1 w-4 h-4 bg-white rounded-full shadow transform transition"></span>
                </button>
            </div>
        </div>
    </form>

    {{-- Info filter aktif --}}
    @if($currentStatus || request('q'))
        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
            @if(request('q'))
                <span class="px-2 py-1 rounded bg-gray-100 text-gray-600">Cari: “{{ request('q') }}”</span>
            @endif
            @if($currentStatus)
                <span class="px-2 py-1 rounded bg-indigo-100 text-indigo-700">Status: {{ ucfirst($currentStatus) }}</span>
            @endif
            <a href="{{ route('admin.tickets.index') }}"
               class="px-2 py-1 rounded bg-white border text-gray-600 hover:bg-gray-50">Reset</a>
        </div>
    @endif

    {{-- Tabel --}}
    @if($tickets->count())
        <div class="overflow-x-auto bg-white border rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-xs font-semibold text-gray-600">
                        <th class="px-4 py-3 text-left uppercase tracking-wider">No Tiket</th>
                        <th class="px-4 py-3 text-left uppercase tracking-wider">Judul</th>
                        <th class="px-4 py-3 text-left uppercase tracking-wider">Reporter</th>
                        <th class="px-4 py-3 text-left uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left uppercase tracking-wider">Dibuat</th>
                        <th class="px-4 py-3 text-right uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tickets as $t)
                        @php
                            $statusKey = strtolower($t->status ?? 'unknown');
                            $badgeClass = $statusColors[$statusKey] ?? 'bg-gray-100 text-gray-700 ring-1 ring-gray-300';
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('admin.tickets.show', $t->id) }}"
                                   class="text-indigo-600 hover:underline">{{ $t->ticket_no }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-800">
                                {{ \Illuminate\Support\Str::limit($t->title, 60) }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                <div>{{ $t->reporter_name }}</div>
                                <div class="text-xs text-gray-400">{{ $t->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full {{ $badgeClass }} font-semibold text-xs">
                                    {{ ucfirst($t->status ?? 'Unknown') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ $t->created_at?->diffForHumans() ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}"
                                       class="text-indigo-600 hover:underline">Lihat</a>
                                    <a href="{{ route('admin.tickets.edit', $t->id) }}"
                                       class="text-gray-500 hover:text-gray-700 small-edit">Edit</a>
                                </div>
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
        <div class="bg-white border rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6M9 16h6M7 8h10M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Belum ada tiket</h3>
            <p class="mt-2 text-sm text-gray-500">Silakan buat tiket baru.</p>
            <div class="mt-4">
                <a href="{{ route('admin.tickets.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700">
                    Buat Tiket Baru
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('editMode');
        if(!toggle) return;
        const dot = toggle.querySelector('.dot');
        let editMode = false;

        function setEditMode(on){
            editMode = on;
            toggle.classList.toggle('bg-indigo-600', on);
            toggle.classList.toggle('bg-gray-300', !on);
            dot.style.transform = on ? 'translateX(20px)' : 'translateX(0)';
            toggle.setAttribute('aria-pressed', String(on));
            document.querySelectorAll('.small-edit').forEach(el => el.classList.toggle('hidden', !on));
        }

        setEditMode(false);
        toggle.addEventListener('click', () => setEditMode(!editMode));
    })();
</script>
@endpush
@endsection
