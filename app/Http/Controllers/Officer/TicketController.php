<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // tambahan: pastikan middleware role:officer ter-apply via routes
    }

    /**
     * List tickets assigned to authenticated officer.
     */
    public function indexAssigned(Request $request)
    {
        $q = $request->query('q');

        $tickets = Ticket::query()
            ->where('assigned_to', Auth::id()) // hanya yang diassign ke officer ini
            ->when($q, fn($query) => $query->where(function($q2) use ($q) {
                $q2->where('ticket_no', 'like', "%{$q}%")
                   ->orWhere('title', 'like', "%{$q}%")
                   ->orWhere('reporter_name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            }))
            ->latest()
            ->paginate(12);

        return view('officer.tickets.index', compact('tickets'));
    }

    /**
     * Show ticket to officer (ensure ownership).
     */
    public function show(Ticket $ticket)
    {
        if ($ticket->assigned_to !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ticket->load(['replies.user', 'assignedTo', 'events.user']); // jika relasi ada
        return view('officer.tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Officer replies to ticket.
     */
    public function reply(Request $request, Ticket $ticket)
    {
        if ($ticket->assigned_to !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
            'status'  => ['nullable', Rule::in(['pending','resolved'])],
        ]);

        DB::beginTransaction();
        try {
            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
            ]);

            // jika ada status dikirimkan dan berbeda, ubah status tiket
            if ($request->filled('status') && $request->status !== $ticket->status) {
                // gunakan helper model agar event tercatat
                $ok = $ticket->setStatus($request->status, Auth::id());
                if (! $ok) {
                    throw new Exception('Gagal menyimpan status tiket');
                }
            }

            $ticket->touch();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Officer reply failed', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim balasan: ' . $e->getMessage());
        }

        return redirect()->route('officer.tickets.show', $ticket->id)
                         ->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Officer updates status (allowed statuses can be restricted)
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        if ((int) $ticket->assigned_to !== (int) Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => ['required', Rule::in(['pending','resolved'])],
        ]);

        Log::info('Officer updateStatus start', [
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'old_status' => $ticket->status,
            'payload' => $request->all(),
        ]);

        DB::beginTransaction();
        try {
            // simpan status langsung (menghindari masalah $fillable)
            $ticket->status = $request->status;
            $ticket->save();

            // catat event; jika gagal, akan dilempar ke catch
            if (method_exists($ticket, 'recordEvent')) {
                $ticket->recordEvent('status_changed', Auth::id(), ['status' => $request->status]);
            }

            DB::commit();

            Log::info('Officer updateStatus success', [
                'ticket_id' => $ticket->id,
                'new_status' => $ticket->status,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Officer updateStatus failed', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal menyimpan status: ' . $e->getMessage());
        }

        return redirect()->route('officer.tickets.show', $ticket->id)
                         ->with('success', 'Status tiket diubah menjadi "' . ucfirst($request->status) . '"');
    }
}
