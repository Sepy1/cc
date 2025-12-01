@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
 

  {{-- Filter Periode (card) --}}
  <div class="mb-6 bg-white p-6 rounded-2xl shadow-md border border-gray-100">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col md:flex-row md:items-end gap-4">
      <div class="flex items-start flex-col">
        <label for="month" class="block text-sm font-medium text-gray-600 mb-1">Bulan</label>
        <select name="month" id="month" class="w-48 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    <option value="all" @selected(($month ?? 'all') == 'all')>Semua</option>
    @foreach(range(1, 12) as $m)
        <option value="{{ $m }}" @selected(($month ?? now()->month) == $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
    @endforeach
</select>
      </div>

      <div class="flex items-start flex-col">
        <label for="year" class="block text-sm font-medium text-gray-600 mb-1">Tahun</label>
        <select name="year" id="year" class="w-36 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
    <option value="all" @selected(($year ?? 'all') == 'all')>Semua</option>
    @foreach($years as $y)
        <option value="{{ $y }}" @selected(($year ?? now()->year) == $y)>{{ $y }}</option>
    @endforeach
</select>

      </div>

      <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Tampilkan</button>
        <a href="{{ route('admin.tickets.index') }}" class="px-4 py-2 border rounded-lg text-sm text-indigo-600 hover:bg-indigo-50">Lihat Semua Tiket</a>
      </div>

     <div class="mt-3 md:mt-0 md:ml-auto text-sm text-gray-600">
    Periode:
    @if(($month ?? 'all') === 'all' && ($year ?? 'all') === 'all')
        <strong>Semua</strong>
    @elseif(($month ?? 'all') === 'all')
        <strong>Semua {{ $year }}</strong>
    @elseif(($year ?? 'all') === 'all')
        <strong>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} Semua Tahun</strong>
    @else
        <strong>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</strong>
    @endif
</div>
    </form>
  </div>

  {{-- Top stats + charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-1 bg-white rounded-2xl shadow p-5 border border-gray-100">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm text-gray-500">Total Tiket</div>
          <div class="mt-2 text-3xl font-bold text-gray-900">{{ $totalTickets }}</div>
          <div class="mt-3 text-xs text-gray-500">Dibuat pada periode pilihan</div>
        </div>
        
      </div>

      <div class="mt-6 grid grid-cols-2 gap-3">
        <div class="p-3 bg-green-50 rounded-lg">
          <div class="text-xs text-gray-600">Open</div>
          <div class="text-lg font-semibold text-green-700">{{ $openCount }}</div>
        </div>
        <div class="p-3 bg-yellow-50 rounded-lg">
          <div class="text-xs text-gray-600">Progress</div>
          <div class="text-lg font-semibold text-yellow-700">{{ $progressCount }}</div>
        </div>
        <div class="p-3 bg-blue-50 rounded-lg">
          <div class="text-xs text-gray-600">Resolved</div>
          <div class="text-lg font-semibold text-blue-700">{{ $resolvedCount }}</div>
        </div>
        <div class="p-3 bg-gray-50 rounded-lg">
          <div class="text-xs text-gray-600">Closed</div>
          <div class="text-lg font-semibold text-gray-700">{{ $closedCount }}</div>
        </div>
      </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl shadow p-5 border border-gray-100">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Tren Tiket</h3>
        <div class="text-sm text-gray-500">Per hari dalam periode</div>
      </div>
      <div class="w-full h-64">
        <canvas id="ticketsTrendChart" class="w-full h-full"></canvas>
      </div>
    </div>
  </div>

  {{-- Status distribution + recent table --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-3 bg-white rounded-2xl shadow p-4 border border-gray-100 overflow-x-auto">
      <div class="p-4 border-b">
        <div class="flex items-center gap-4">
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-medium text-gray-900">Tiket Terbaru</h3>
            <div class="text-sm text-gray-600">
              Periode:
              @if(($month ?? 'all') === 'all' && ($year ?? 'all') === 'all')
                  <strong>Semua</strong>
              @elseif(($month ?? 'all') === 'all')
                  <strong>Semua {{ $year }}</strong>
              @elseif(($year ?? 'all') === 'all')
                  <strong>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} Semua Tahun</strong>
              @else
                  <strong>{{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }} {{ $year }}</strong>
              @endif
            </div>
          </div>
          <div class="shrink-0">
            <a href="{{ route('admin.reports.export_csv', ['month' => request('month', $month ?? 'all'), 'year' => request('year', $year ?? 'all')]) }}"
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
              </svg>
              Download CSV
            </a>
          </div>
        </div>
      </div>

      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
          <tr class="text-xs font-semibold text-gray-600 uppercase">
            <th class="px-4 py-3 text-left">No Tiket</th>
            <th class="px-4 py-3 text-left">Judul</th>
            <th class="px-4 py-3 text-left">Pelapor</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Tanggal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($recent as $t)
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-medium">
                <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-indigo-600 hover:underline">{{ $t->ticket_no }}</a>
              </td>
              <td class="px-4 py-3 text-gray-800">{{ \Illuminate\Support\Str::limit($t->title, 60) }}</td>
              <td class="px-4 py-3 text-gray-700">{{ $t->reporter_name ?? ($t->user->name ?? '—') }}</td>
              <td class="px-4 py-3">
                @php
                  $statusColors = [
                    'open' => 'bg-green-50 text-green-700',
                    'progress' => 'bg-yellow-50 text-yellow-700',
                    'resolved' => 'bg-blue-50 text-blue-700',
                    'closed' => 'bg-gray-100 text-gray-700',
                    'rejected' => 'bg-red-50 text-red-700',
                  ];
                  $badge = $statusColors[$t->status] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($t->status) }}</span>
              </td>
              <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d M Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada tiket pada periode ini</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Scripts: Chart.js (CDN) --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data from controller.
@php
    $ticketsByDay = $ticketsByDay ?? [];
@endphp
const ticketsByDay = @json($ticketsByDay);

  // Line chart (trend)
  (function(){
    const ctx = document.getElementById('ticketsTrendChart').getContext('2d');
    const labels = ticketsByDay.map(d => d.date);
    const data = ticketsByDay.map(d => d.count);

    new Chart(ctx, {
      type: 'line',
      data: { labels, datasets: [{ label: 'Tiket per hari', data, fill: true, tension: 0.3, pointRadius: 3 }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { x: { display: true }, y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
      }
    });
  })();
</script>
@endpush

@endsection
