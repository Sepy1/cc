<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\TicketAssigned;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Ticket;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Schema;
use App\Models\TicketEvent;

class TicketAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $tickets = Ticket::query()
            ->when($q, function ($query, $q) {
                $query->where('ticket_no', 'like', "%{$q}%")
                      ->orWhere('reporter_name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('title', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(12);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('admin.tickets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reporter_name' => 'required|string|max:255',
            'phone'         => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:255',
            'category'      => 'nullable|string|max:150',
            'title'         => 'required|string|max:255',
            'detail'        => 'nullable|string',
            'status'        => 'nullable|string|in:open,pending,resolved,closed,rejected',
        ]);

        $data['ticket_no'] = $this->generateTicketNo();
        $data['status'] = $data['status'] ?? 'open';

        $ticket = Ticket::create($data);

        // record event: created
        if (class_exists(TicketEvent::class)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'type'      => 'created',
                'meta'      => json_encode([
                    'ticket_no' => $ticket->ticket_no,
                    'title'     => $ticket->title,
                    'reporter'  => $ticket->reporter_name,
                ]),
                'user_id'   => auth()->id(),
            ]);
        }

        return redirect()->route('admin.tickets.show', $ticket->id)
                         ->with('success', 'Tiket berhasil dibuat.');
    }

    // method show
    public function show(Ticket $ticket)
    {
        // ambil semua user dengan role officer
        $officers = User::where('role', 'officer')->orderBy('name')->get();

        // eager load replies and assignedTo if relation exists
        $ticket->loadMissing('replies', 'assignedTo', 'events');

        return view('admin.tickets.show', compact('ticket', 'officers'));
    }

    public function edit(Ticket $ticket)
    {
        return view('admin.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'detail' => 'nullable|string',
            'status' => ['required', Rule::in(['open','pending','resolved','closed','rejected'])],
            'assigned_to' => 'nullable|integer|exists:users,id',
        ];

        // jika memilih closed, tindak_lanjut wajib
        if ($request->input('status') === 'closed') {
            $rules['tindak_lanjut'] = 'required|string|max:2000';
        } else {
            $rules['tindak_lanjut'] = 'nullable|string|max:2000';
        }

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            $originalStatus = $ticket->status;

            // simpan tindak_lanjut secara eksplisit bila ada
            if (!empty($data['tindak_lanjut'])) {
                $ticket->tindak_lanjut = $data['tindak_lanjut'];
            }

            // simpan field lain (jangan gunakan array_except, langsung assign)
            foreach ($data as $key => $value) {
                if ($key !== 'tindak_lanjut' && $ticket->isFillable($key)) {
                    $ticket->$key = $value;
                }
            }
            $ticket->save();

            // catat event jika status berubah
            if (isset($data['status']) && $originalStatus !== $ticket->status) {
                // bila status berubah, gunakan helper model untuk mencatat event (ikutkan tindak_lanjut)
                $ticket->setStatus($ticket->status, auth()->id(), $data['tindak_lanjut'] ?? null);
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Admin ticket update failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal menyimpan tiket: ' . $e->getMessage());
        }

        return redirect()->route('admin.tickets.show', $ticket->id)->with('success', 'Tiket diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        // record deletion event BEFORE deleting (so ticket_id exists)
        if (class_exists(TicketEvent::class)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'type'      => 'deleted',
                'meta'      => json_encode([
                    'ticket_no' => $ticket->ticket_no,
                    'title'     => $ticket->title,
                ]),
                'user_id'   => auth()->id(),
            ]);
        }

        $ticket->delete();

        return redirect()->route('admin.tickets.index')
                         ->with('success', 'Tiket dihapus.');
    }

    protected function generateTicketNo()
    {
        return 'T-' . now()->format('Ymd') . '-' . Str::lower(Str::random(6));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $reply = TicketReply::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(), // admin yang sedang login
            'message'     => $request->message,
        ]);

        // Opsional: update updated_at ticket
        $ticket->touch();

        // record event: replied
        if (class_exists(TicketEvent::class)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'type'      => 'replied',
                'meta'      => json_encode([
                    'reply_id' => $reply->id,
                    'snippet'  => \Illuminate\Support\Str::limit($request->message, 200),
                ]),
                'user_id'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Assign ticket to an officer
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'officer_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ]);

        $officer = User::where('id', $data['officer_id'])->where('role', 'officer')->first();

        if (! $officer) {
            return redirect()->route('admin.tickets.show', $ticket->id)
                             ->with('error', 'Officer tidak ditemukan atau tidak memiliki role officer.');
        }

        DB::beginTransaction();
        try {
            $ticket->assigned_to = $officer->id;

            if (Schema::hasColumn($ticket->getTable(), 'assigned_at')) {
                $ticket->assigned_at = now();
            }

            $ticket->save();

            if (class_exists(TicketEvent::class)) {
                TicketEvent::create([
                    'ticket_id' => $ticket->id,
                    'type'      => 'assigned',
                    'meta'      => json_encode([
                        'assigned_to' => $officer->id,
                        'assigned_to_name' => $officer->name,
                    ]),
                    'user_id'   => auth()->id(),
                ]);
            }

            if (class_exists(TicketAssigned::class)) {
                try {
                    Mail::to($officer->email)->send(new TicketAssigned($ticket, $officer));
                } catch (\Throwable $e) {
                    // jangan gagalkan assign karena email gagal
                    \Log::warning('Kirim email TicketAssigned gagal: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()->route('admin.tickets.show', $ticket->id)
                             ->with('success', 'Tiket berhasil diassign ke ' . $officer->name . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Assign ticket failed: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'officer_id' => $data['officer_id'],
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('admin.tickets.show', $ticket->id)
                             ->with('error', 'Terjadi kesalahan saat assign tiket. Silakan coba lagi.');
        }
    }

    /**
     * Quick change ticket status (Admin)
     */
    public function changeStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(['open','pending','resolved','closed','rejected'])],
        ]);

        $old = $ticket->status;
        $ticket->status = $data['status'];
        $ticket->save();

        if (class_exists(TicketEvent::class)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'type'      => 'status_change',
                'meta'      => json_encode(['from' => $old, 'to' => $data['status']]),
                'user_id'   => auth()->id(),
            ]);
        }

        return redirect()
            ->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Status tiket diubah menjadi "' . ucfirst($data['status']) . '"');
    }
}
