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
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketReply;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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
        $ticket->load(['replies.user']);

        // ambil riwayat dari ticket_events
        $history = DB::table('ticket_events')
            ->where('ticket_id', $ticket->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // jika Anda sudah punya model TicketEvent, bisa gunakan relation:
        // $history = $ticket->events()->latest()->get();

        // ambil semua user dengan role officer
        $officers = User::where('role', 'officer')->orderBy('name')->get();

        // eager load replies and assignedTo if relation exists
        $ticket->loadMissing('replies', 'assignedTo', 'events');

        return view('admin.tickets.show', compact('ticket','history', 'officers'));
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

            // set closing_at berdasarkan status terkini
            if (in_array($ticket->status, ['resolved','closed'])) {
                $ticket->closing_at = now();
            } else {
                $ticket->closing_at = null;
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

        // flash notif status change (admin)
        if (isset($data['status']) && $originalStatus !== $ticket->status) {
            session()->flash('notif', [
                'type' => 'status',
                'message' => 'Status tiket diubah dari ' . ucfirst($originalStatus) . ' ke ' . ucfirst($ticket->status),
            ]);
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
            'message'     => 'nullable|string|max:5000',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $ext  = $file->getClientOriginalExtension();
            $path = $file->storeAs('ticket_attachments', "{$name}.{$ext}", 'public'); // storage/app/public/ticket_attachments
        }

        $reply = TicketReply::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => Auth::id(),
            'message'    => $request->message,
            'attachment' => $path,
        ]);

        // update updated_at tiket
        $ticket->touch();

        // flash notif reply (admin)
        session()->flash('notif', [
            'type' => 'reply',
            'message' => 'Komentar baru dikirim ke tiket #' . $ticket->ticket_no,
        ]);

        return redirect()
            ->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Balasan tersimpan' . ($path ? ' dengan lampiran.' : '.'));
    }

    /**
     * Assign ticket to an officer
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
        ]);

        $assignee = \App\Models\User::findOrFail($data['user_id']);

        try {
            DB::beginTransaction();

            $ticket->assigned_to = $assignee->id;
            $ticket->assigned_at = now();
            if (in_array($ticket->status, ['open','rejected'])) {
                $ticket->status = 'pending';
            }
            $ticket->save();

            // catat riwayat assign
            try {
                DB::table('ticket_events')->insert([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'type'      => 'assigned',
                    'meta'      => json_encode([
                        'assigned_to'      => $assignee->id,
                        'assigned_to_name' => $assignee->name,
                    ]),
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('ticket_events_insert_failed', ['ticket_id'=>$ticket->id,'error'=>$e->getMessage()]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('assign_ticket_failed', ['ticket_id'=>$ticket->id,'error'=>$e->getMessage()]);
            return back()->with('error', 'Gagal assign tiket. Detail: '.$e->getMessage());
        }

        // kirim email jika perlu (dibuat non-blocking)
        try {
            if (class_exists(\App\Mail\TicketAssigned::class) && !empty($assignee->email)) {
                \Illuminate\Support\Facades\Mail::to($assignee->email)->queue(new \App\Mail\TicketAssigned($ticket));
            }
        } catch (\Throwable $e) {
            Log::warning('assign_mail_failed', ['ticket_id'=>$ticket->id,'error'=>$e->getMessage()]);
        }

        return back()->with('success', 'Tiket berhasil di-assign ke '.$assignee->name);
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
        // set closing_at sesuai status baru
        $ticket->closing_at = in_array($ticket->status, ['resolved','closed']) ? now() : null;
        $ticket->save();

        if (class_exists(TicketEvent::class)) {
            TicketEvent::create([
                'ticket_id' => $ticket->id,
                'type'      => 'status_change',
                'meta'      => json_encode(['from' => $old, 'to' => $data['status']]),
                'user_id'   => auth()->id(),
            ]);
        }

        // flash notif quick status (admin)
        session()->flash('notif', [
            'type' => 'status',
            'message' => 'Status tiket diubah dari ' . ucfirst($old) . ' ke ' . ucfirst($ticket->status),
        ]);

        return redirect()
            ->route('admin.tickets.show', $ticket->id)
            ->with('success', 'Status tiket diubah menjadi "' . ucfirst($data['status']) . '"');
    }
}
