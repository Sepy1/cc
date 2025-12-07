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
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold">Tiket Saya</h1>
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
    // Cleanup: no bell/panel scripts
})();
</script>
@endpush
@endsection
