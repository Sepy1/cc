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
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Tiket Saya</h1>
            <p class="text-sm text-gray-500">Tiket yang diassign kepada Anda.</p>
        </div>

        <form action="{{ route('officer.tickets.index') }}" method="GET" class="flex items-center gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari tiket..." class="px-3 py-2 border rounded-md" />
            <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Cari</button>
        </form>
    </div>

    @if($tickets->count())
    <div class="bg-white border rounded-lg shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No Tiket</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Judul</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pelapor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tickets as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                        <a href="{{ route('officer.tickets.show', $t->id) }}" class="text-indigo-600 hover:underline">{{ $t->ticket_no }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ \Illuminate\Support\Str::limit($t->title,60) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <div>{{ $t->reporter_name }}</div>
                        <div class="text-xs text-gray-400">{{ $t->email ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @php $badgeClass = $statusColors[strtolower($t->status ?? 'unknown')] ?? 'bg-gray-100 text-gray-700'; @endphp
                        <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded {{ $badgeClass }}">{{ ucfirst($t->status ?? 'Unknown') }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $t->created_at?->diffForHumans() ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-sm">
                        <a href="{{ route('officer.tickets.show', $t->id) }}" class="text-indigo-600 hover:underline">Lihat</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $tickets->withQueryString()->links() }}</div>
    @else
        <div class="bg-white border rounded-lg p-8 text-center">
            <h3 class="text-lg font-medium text-gray-900">Belum ada tiket</h3>
            <p class="text-sm text-gray-500">Tidak ada tiket yang diassign ke Anda.</p>
        </div>
    @endif
</div>
@endsection
