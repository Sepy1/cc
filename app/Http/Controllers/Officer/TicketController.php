<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
// tambahkan import model balasan
use App\Models\TicketReply;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // role:officer diterapkan via routes
    }

    // INDEX: tiket yang di-assign ke officer saat ini + pencarian
    public function index(Request $request)
    {
        $q = $request->query('q');

        $tickets = Ticket::query()
            ->where('assigned_to', Auth::id())
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

    public function create()
    {
        return view('officer.tickets.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // field utama
            'title'            => 'required|string|max:255',
            'category'         => 'nullable|string|max:100',
            'detail'           => 'nullable|string',
            'reporter_name'    => 'required|string|max:150',
            'email'            => 'nullable|email|max:150',
            'phone'            => 'nullable|string|max:50',
            // pelapor & nasabah
            'reporter_type'    => 'required|in:nasabah,umum',
            'is_nasabah'       => 'nullable|boolean',
            'id_ktp'           => 'nullable|string|max:100',
            'nomor_rekening'   => 'nullable|string|max:100',
            'nama_ibu'         => 'nullable|string|max:150',
            'alamat'           => 'nullable|string|max:2000',
            'kode_kantor'      => 'nullable|string|max:50',
            'media_closing'    => 'nullable|in:whatsapp,telepon,offline',
            // lampiran
            'attachment_ktp'   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'attachment_bukti' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,zip',
        ]);

        $data['ticket_no']  = 'TCK-' . strtoupper(Str::random(6));
        $data['status']     = 'open';
        $data['is_nasabah'] = ($data['reporter_type'] ?? '') === 'nasabah';
        $data['assigned_to'] = Auth::id();

        if ($request->hasFile('attachment_ktp')) {
            $data['attachment_ktp'] = $this->storeAttachment($request->file('attachment_ktp'), 'tickets/ktp');
        }
        if ($request->hasFile('attachment_bukti')) {
            $data['attachment_bukti'] = $this->storeAttachment($request->file('attachment_bukti'), 'tickets/bukti');
        }

        $ticket = Ticket::create($data);

        return redirect()->route('officer.tickets.show', $ticket)->with('status', 'Tiket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)\Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Eager-load relasi seperti semula
        $ticket->loadMissing(['replies.user', 'events.user', 'assignedTo']);

        return view('officer.tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('officer.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'category'         => 'nullable|string|max:100',
            'detail'           => 'nullable|string',
            'reporter_name'    => 'sometimes|required|string|max:150',
            'email'            => 'nullable|email|max:150',
            'phone'            => 'nullable|string|max:50',
            // pelapor & nasabah
            'reporter_type'    => 'required|in:nasabah,umum',
            'is_nasabah'       => 'nullable|boolean',
            'id_ktp'           => 'nullable|string|max:100',
            'nomor_rekening'   => 'nullable|string|max:100',
            'nama_ibu'         => 'nullable|string|max:150',
            'alamat'           => 'nullable|string|max:2000',
            'kode_kantor'      => 'nullable|string|max:50',
            'media_closing'    => 'nullable|in:whatsapp,telepon,offline',
            'attachment_ktp'   => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf',
            'attachment_bukti' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,zip',
        ]);

        $data['is_nasabah'] = ($data['reporter_type'] ?? $ticket->reporter_type) === 'nasabah';

        if ($request->hasFile('attachment_ktp')) {
            if ($ticket->attachment_ktp) Storage::disk('public')->delete($ticket->attachment_ktp);
            $data['attachment_ktp'] = $this->storeAttachment($request->file('attachment_ktp'), 'tickets/ktp');
        }
        if ($request->hasFile('attachment_bukti')) {
            if ($ticket->attachment_bukti) Storage::disk('public')->delete($ticket->attachment_bukti);
            $data['attachment_bukti'] = $this->storeAttachment($request->file('attachment_bukti'), 'tickets/bukti');
        }

        $ticket->update($data);

        return redirect()->route('officer.tickets.show', $ticket)->with('status', 'Tiket berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // hapus lampiran jika ada
        if ($ticket->attachment_ktp) Storage::disk('public')->delete($ticket->attachment_ktp);
        if ($ticket->attachment_bukti) Storage::disk('public')->delete($ticket->attachment_bukti);

        $ticket->delete();
        return redirect()->route('officer.tickets.index')->with('status', 'Tiket dihapus.');
    }

    // Update status (Pending/Resolved) oleh officer pemilik tiket
    public function updateStatus(Request $request, Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => ['required', Rule::in(['pending', 'resolved'])],
        ]);

        Log::info('Officer updateStatus start', [
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'old_status' => $ticket->status,
            'payload' => $request->all(),
        ]);

        DB::beginTransaction();
        try {
            $old = $ticket->status;
            $ticket->status = $request->status;
            // tentukan closing_at: resolved => now, pending => null
            $ticket->closing_at = ($request->status === 'resolved') ? now() : null;
            $ticket->save();

            if (method_exists($ticket, 'recordEvent')) {
                $ticket->recordEvent('status_changed', Auth::id(), ['status' => $request->status]);
            }

            // Notify all admins about status change
            try {
                $adminIds = \App\Models\User::where('role', 'admin')->pluck('id');
                foreach ($adminIds as $adminId) {
                    \Illuminate\Support\Facades\DB::table('notifications')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => 'ticket.status_changed',
                        'notifiable_type' => \App\Models\User::class,
                        'notifiable_id' => $adminId,
                        'data' => json_encode([
                            'ticket_id' => $ticket->id,
                            'message' => "Status tiket {$ticket->ticket_no} diubah menjadi " . ucfirst($ticket->status),
                            'url' => route('admin.tickets.show', $ticket->id),
                        ], JSON_UNESCAPED_UNICODE),
                        'read_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('notif_insert_failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Officer updateStatus failed', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal menyimpan status: ' . $e->getMessage());
        }

        // Kirim notif sebagai flash (satu sumber)
return redirect()->route('officer.tickets.show', $ticket->id)
    ->with('notif', [
        'type' => 'status',
        'message' => 'Status tiket diubah dari ' . ucfirst($old) . ' ke ' . ucfirst($ticket->status),
    ])
    ->with('success', 'Status tiket diubah menjadi "' . ucfirst($request->status) . '"');

    }

    // Balasan officer (simpan ke DB)
    public function reply(Request $request, Ticket $ticket)
    {
        if ((int)$ticket->assigned_to !== (int)Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'message'    => 'nullable|string|max:10000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip',
        ]);

        // Minimal salah satu harus ada
        if (blank($validated['message'] ?? null) && !$request->hasFile('attachment')) {
            return back()->withErrors(['message' => 'Isi pesan atau unggah lampiran.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $this->storeAttachment($request->file('attachment'), 'tickets/replies');
        }

        // Simpan reply (gunakan relasi jika tersedia)
        try {
            if (method_exists($ticket, 'replies')) {
                $ticket->replies()->create([
                    'user_id'    => Auth::id(),
                    'message'    => $validated['message'] ?? null,
                    'attachment' => $path,
                ]);
            } else {
                // Fallback langsung pakai model (pastikan table/fillable sudah sesuai)
                $reply = new TicketReply();
                $reply->ticket_id  = $ticket->id;
                $reply->user_id    = Auth::id();
                $reply->message    = $validated['message'] ?? null;
                $reply->attachment = $path;
                $reply->save();
            }
        } catch (Exception $e) {
            // hapus file bila gagal simpan
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            return back()->withErrors(['message' => 'Gagal menyimpan balasan: ' . $e->getMessage()])->withInput();
        }

        // Opsional: catat event
        if (method_exists($ticket, 'recordEvent')) {
            $ticket->recordEvent('replied', Auth::id(), [
                'snippet' => \Illuminate\Support\Str::limit((string)($validated['message'] ?? ''), 120),
            ]);
        }

        // Notify all admins about new comment
        try {
            $adminIds = \App\Models\User::where('role', 'admin')->pluck('id');
            foreach ($adminIds as $adminId) {
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'ticket.replied',
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id' => $adminId,
                    'data' => json_encode([
                        'ticket_id' => $ticket->id,
                        'message' => "Komentar baru pada tiket {$ticket->ticket_no}",
                        'url' => route('admin.tickets.show', $ticket->id),
                    ], JSON_UNESCAPED_UNICODE),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('notif_insert_failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
        }

        // flash notif reply (officer)
        return back()->with('notif', [
    'type' => 'reply',
    'message' => 'Balasan terkirim ke tiket #' . $ticket->ticket_no,
]);
    }

    public function showProfile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('officer.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable','confirmed','min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'Profil diperbarui.');
    }

    protected function storeAttachment($file, string $dir): string
    {
        $name = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($dir, $name, 'public');
    }
}
