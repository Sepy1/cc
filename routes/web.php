<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Alias controllers
use App\Http\Controllers\Admin\TicketAdminController as AdminTicketController;
use App\Http\Controllers\Officer\TicketController as OfficerTicketController;
use App\Http\Controllers\Admin\ReportController;

/*
|--------------------------------------------------------------------------
| Public / Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
      return redirect()->route('login');
      });

/*
|--------------------------------------------------------------------------
| Dashboard redirect (after login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.tickets.index');
    }

    if ($user->role === 'officer') {
        return redirect()->route('officer.tickets.index');
    }

    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin area (only role:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Tickets - list
        Route::get('/tickets', [AdminTicketController::class, 'index'])
            ->name('tickets.index');

        // Create / Store
        Route::get('/tickets/create', [AdminTicketController::class, 'create'])
            ->name('tickets.create');
        Route::post('/tickets', [AdminTicketController::class, 'store'])
            ->name('tickets.store');

        // Show (detail) - ticket id numeric only
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])
            ->whereNumber('ticket')
            ->name('tickets.show');

        // Edit / Update
        Route::get('/tickets/{ticket}/edit', [AdminTicketController::class, 'edit'])
            ->whereNumber('ticket')
            ->name('tickets.edit');
        Route::put('/tickets/{ticket}', [AdminTicketController::class, 'update'])
            ->whereNumber('ticket')
            ->name('tickets.update');

        // Delete
        Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])
            ->whereNumber('ticket')
            ->name('tickets.destroy');

        // Assign officer
        Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\Admin\TicketAdminController::class, 'assign'])
            ->whereNumber('ticket')
            ->name('tickets.assign');

        // Admin reply
        Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Admin\TicketAdminController::class, 'reply'])
            ->whereNumber('ticket')
            ->name('tickets.reply');

        // Quick status change (admin)
        Route::post('/tickets/{ticket}/status', [AdminTicketController::class, 'changeStatus'])
            ->whereNumber('ticket')
            ->name('tickets.change_status');

        // Laporan (reports)
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        // Export: XLS/XLSX (if implemented)
        Route::get('/reports/export', [ReportController::class, 'export'])
            ->name('reports.export');

        // Export: PDF laporan bulanan
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.export_pdf');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Admin\TicketAdminController::class, 'showProfile'])->name('profile.show');
        Route::post('/profile', [\App\Http\Controllers\Admin\TicketAdminController::class, 'updateProfile'])->name('profile.update');

        // Export Excel (all columns) without external packages
        Route::get('/reports/export-excel', function (\Illuminate\Http\Request $request) {
            $month = $request->query('month', 'all');
            $year  = $request->query('year', 'all');

            $q = \Illuminate\Support\Facades\DB::table('tickets');
            if ($month !== 'all' && is_numeric($month)) $q->whereMonth('created_at', (int)$month);
            if ($year !== 'all' && is_numeric($year))   $q->whereYear('created_at', (int)$year);

            $rows = $q->orderByDesc('created_at')->get();
            // filter out attachment columns
            $cols = array_values(array_filter(
                \Illuminate\Support\Facades\Schema::getColumnListing('tickets'),
                fn($c) => !in_array($c, ['attachment_ktp','attachment_bukti'])
            ));

            $html = '<table border="1"><thead><tr>';
            foreach ($cols as $c) { $html .= '<th>'.htmlspecialchars($c, ENT_QUOTES, 'UTF-8').'</th>'; }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $r) {
                $html .= '<tr>';
                foreach ($cols as $c) {
                    $val = $r->$c ?? '';
                    if (is_bool($val)) $val = $val ? '1' : '0';
                    if (is_object($val) || is_array($val)) $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    $html .= '<td>'.htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8').'</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            $filename = 'tickets_'.date('Ymd_His').'.xls';
            return response("\xEF\xBB\xBF".$html)
                ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        })->name('reports.export_excel');
    });

/*
|--------------------------------------------------------------------------
| Officer area (only role:officer)
|--------------------------------------------------------------------------
|
| NOTE: controller should verify that the authenticated officer is allowed
| to view/update the requested ticket (e.g. ticket->assigned_to === auth()->id()).
|
*/
Route::middleware(['auth', 'role:officer'])
    ->prefix('officer')
    ->name('officer.')
    ->group(function () {
        Route::resource('tickets', \App\Http\Controllers\Officer\TicketController::class);
        Route::post('tickets/{ticket}/reply', [\App\Http\Controllers\Officer\TicketController::class, 'reply'])->name('tickets.reply');
        Route::post('tickets/{ticket}/update-status', [\App\Http\Controllers\Officer\TicketController::class, 'updateStatus'])->name('tickets.update_status');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Officer\TicketController::class, 'showProfile'])->name('profile.show');
        Route::post('/profile', [\App\Http\Controllers\Officer\TicketController::class, 'updateProfile'])->name('profile.update');
    });

/*
|--------------------------------------------------------------------------
| Auth (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

// Notifications: mark as read (auth required)
Route::middleware(['auth'])->post('/notifications/{id}/read', function ($id) {
    $user = Auth::user();
    \Illuminate\Support\Facades\DB::table('notifications')
        ->where('id', $id)
        ->where('notifiable_id', $user->id)
        ->update(['read_at' => now()]);
    return response()->noContent();
})->name('notifications.read');

// Notifications: list (auth required) - returns unread/read latest 20
Route::middleware(['auth'])->get('/notifications/list', function () {
    $user = Auth::user();
    $unread = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->whereNull('read_at')
        ->orderByDesc('created_at')
        ->limit(20)
        ->get();
    $read = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_id', $user->id)
        ->whereNotNull('read_at')
        ->orderByDesc('created_at')
        ->limit(20)
        ->get();

    return response()->json([
        'unread' => $unread,
        'read'   => $read,
        'count'  => $unread->count(),
    ]);
})->name('notifications.list');
