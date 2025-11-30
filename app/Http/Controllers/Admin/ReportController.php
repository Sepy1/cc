<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\TicketsExport;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Menampilkan halaman laporan admin
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
   public function index(Request $request)
{
    // pastikan hanya admin
    if ((Auth::user()->role ?? '') !== 'admin') {
        abort(403, 'Unauthorized');
    }

    // ambil filter (default 'all' agar view yg baru bekerja)
    $month = $request->input('month', 'all'); // 'all' atau 1..12
    $year  = $request->input('year', 'all');  // 'all' atau 2025

    // mulai builder dasar (tidak langsung whereYear/whereMonth)
    $baseQuery = Ticket::query();

    // apply filter jika tidak 'all'
    if ($year !== 'all') {
        $baseQuery->whereYear('created_at', (int) $year);
    }
    if ($month !== 'all') {
        $baseQuery->whereMonth('created_at', (int) $month);
    }

    // total tiket pada periode (atau semua jika 'all')
    $totalTickets = (clone $baseQuery)->count();

    // hitung per status (open/progress/resolved/closed/rejected)
    $statusCounts = (clone $baseQuery)
        ->select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->pluck('count', 'status')
        ->toArray();

    $openCount     = $statusCounts['open'] ?? 0;
    $progressCount  = $statusCounts['progress'] ?? 0;
    $resolvedCount = $statusCounts['resolved'] ?? 0;
    $closedCount   = $statusCounts['closed'] ?? 0;
    $rejectedCount = $statusCounts['rejected'] ?? 0;

    // Recent tickets (10 terakhir) within applied period
    $recent = (clone $baseQuery)->latest()->take(10)->get();

    // Siapkan ticketsByStatus untuk chart
    $ticketsByStatus = [
        'open'     => $openCount,
        'progress'  => $progressCount,
        'resolved' => $resolvedCount,
        'closed'   => $closedCount,
    ];

    //
    // Siapkan ticketsByDay (atau month/year grouping) untuk line chart
    //
    // Strategy:
    // - Jika month != 'all' AND year != 'all' -> group by DATE(created_at) (days in that month)
    // - Elseif month == 'all' AND year != 'all' -> group by month in that year (YYYY-MM)
    // - Else (year == 'all') -> group by month for last 12 months (to keep chart readable)
    //
    if ($year !== 'all' && $month !== 'all') {
        // group per hari for the selected month
        $ticketsByDayQuery = (clone $baseQuery)
            ->select(DB::raw('DATE(created_at) as label'), DB::raw('count(*) as count'))
            ->groupBy('label')
            ->orderBy('label');
        $ticketsByDayRaw = $ticketsByDayQuery->get();
        // map into date,count
        $ticketsByDay = $ticketsByDayRaw->map(function ($r) {
            return ['date' => $r->label, 'count' => (int)$r->count];
        })->toArray();
    } elseif ($year !== 'all' && $month === 'all') {
        // group per month for that year (YYYY-MM)
        $ticketsByDayRaw = (clone $baseQuery)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label"), DB::raw('count(*) as count'))
            ->groupBy('label')
            ->orderBy('label')
            ->get();
        $ticketsByDay = $ticketsByDayRaw->map(function ($r) {
            return ['date' => $r->label . '-01', 'count' => (int)$r->count]; // normalize to first day of month
        })->toArray();
    } else {
        // year == 'all' -> show last 12 months grouped per month (more useful than per-year)
        $end = Carbon::now()->endOfMonth();
        $start = (clone $end)->subMonths(11)->startOfMonth(); // last 12 months

        $ticketsByDayRaw = Ticket::whereBetween('created_at', [$start->toDateString(), $end->toDateString()])
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label"), DB::raw('count(*) as count'))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // build map for months to guarantee zero values for missing months
        $period = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $period[$cursor->format('Y-m')] = 0;
            $cursor->addMonth();
        }

        foreach ($ticketsByDayRaw as $r) {
            $period[$r->label] = (int)$r->count;
        }

        $ticketsByDay = [];
        foreach ($period as $ym => $count) {
            $ticketsByDay[] = ['date' => $ym . '-01', 'count' => $count];
        }
    }

    // daftar tahun untuk dropdown (mis. from earliest ticket year to now)
    // lebih dinamis: ambil tahun minimal dari tabel ticket supaya 'Semua' berfungsi
    $minYear = Ticket::min(DB::raw('YEAR(created_at)')) ?: now()->year;
    $years = range(now()->year, max($minYear, now()->year - 5)); // at least last 5 years or more if data exists

    // statusList untuk view (untuk menghindari array literal di blade)
    $statusList = [
        'Open'     => $openCount,
        'Progress' => $progressCount,
        'Resolved' => $resolvedCount,
        'Closed'   => $closedCount,
    ];

    // return view
    return view('admin.reports.index', compact(
        'totalTickets',
        'statusCounts',    // optional
        'ticketsByStatus',
        'ticketsByDay',
        'openCount',
        'progressCount',
        'resolvedCount',
        'closedCount',
        'rejectedCount',
        'recent',
        'month',
        'year',
        'years',
        'statusList'
    ));
}

public function export(Request $request)
{
    // authorize (same check as index)
    if ((Auth::user()->role ?? '') !== 'admin') {
        abort(403);
    }

    // get filters
    $month = $request->input('month', 'all');
    $year  = $request->input('year', 'all');

    // build query with same rules as index (don't apply where if 'all')
    $query = \App\Models\Ticket::query();
    if ($year !== 'all') {
        $query->whereYear('created_at', (int) $year);
    }
    if ($month !== 'all') {
        $query->whereMonth('created_at', (int) $month);
    }

    // order and fetch (for large datasets consider FromQuery or chunked approach)
    $tickets = $query->orderBy('created_at', 'desc')->get();

    // build filename
    $labelMonth = ($month === 'all') ? 'allmonths' : sprintf('%02d', (int)$month);
    $labelYear  = ($year === 'all') ? 'allyears' : $year;
    $filename = sprintf('tickets_%s_%s_%s.xlsx', $labelYear, $labelMonth, date('Ymd_His'));

    return Excel::download(new TicketsExport($tickets), $filename);
}

public function exportCsv(Request $request)
{
    // hanya admin
    if ((Auth::user()->role ?? '') !== 'admin') {
        abort(403);
    }

    $month = $request->input('month', 'all');
    $year  = $request->input('year', 'all');

    $query = \App\Models\Ticket::query();
    if ($year !== 'all') {
        $query->whereYear('created_at', (int) $year);
    }
    if ($month !== 'all') {
        $query->whereMonth('created_at', (int) $month);
    }

    // ambil data (batasi jika db sangat besar - atau gunakan chunking)
    $tickets = $query->orderBy('created_at', 'desc')->get();

    $filename = sprintf(
        'tickets_%s_%s_%s.csv',
        $year === 'all' ? 'allyears' : $year,
        $month === 'all' ? 'allmonths' : sprintf('%02d', (int)$month),
        date('Ymd_His')
    );

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $columns = [
        'No Tiket','Pelapor','Phone','Email','Kategori','Judul','Detail','Status','Assigned To','Tindak Lanjut','Tanggal Dibuat'
    ];

    $callback = function() use ($tickets, $columns) {
        $fh = fopen('php://output', 'w');
        // Optional: BOM supaya Excel mengenali UTF-8
        fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($fh, $columns);

        foreach ($tickets as $t) {
            fputcsv($fh, [
                $t->ticket_no,
                $t->reporter_name ?? '',
                $t->phone ?? '',
                $t->email ?? '',
                $t->category ?? '',
                $t->title ?? '',
                $t->detail ?? '',
                ucfirst($t->status ?? ''),
                $t->assigned_to ?? '',
                $t->tindak_lanjut ?? '',
                $t->created_at ? $t->created_at->format('Y-m-d H:i:s') : '',
            ]);
        }
        fclose($fh);
    };

    return response()->stream($callback, 200, $headers);
}

}
