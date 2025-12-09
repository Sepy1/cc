<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Ticket;
use App\Models\TicketEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Store a newly created ticket in storage.
     */
    public function store(StoreTicketRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Paksa default reporter_type dari form statis: nasabah
        $data['reporter_type'] = $data['reporter_type'] ?? 'nasabah';
        // Set status default
        $data['status'] = $data['status'] ?? 'open';

        // is_nasabah diset otomatis jika reporter_type nasabah
        $data['is_nasabah'] = ($data['reporter_type'] === 'nasabah');

        // media_closing konsisten lowercase (whatsapp/telepon/email/offline)
        if (isset($data['media_closing']) && is_string($data['media_closing'])) {
            $mc = strtolower(trim($data['media_closing']));
            $data['media_closing'] = $mc === 'telephone' ? 'telepon' : $mc; // konsisten label
        }

        // Merge field tambahan yang mungkin tidak ada di StoreTicketRequest
        $extra = [
            'tempat_lahir'   => $request->input('tempat_lahir'),
            'tgl_lahir'      => $request->input('tgl_lahir'),
            'id_ktp'         => $request->input('id_ktp') ?? $request->input('ktp_id'), // alias
            'alamat'         => $request->input('alamat'),
            'nomor_rekening' => $request->input('nomor_rekening'),
            'nama_ibu'       => $request->input('nama_ibu'),
            'kode_kantor'    => $request->input('kode_kantor'),
            'is_nasabah'     => $request->boolean('is_nasabah', $data['is_nasabah'] ?? null),
        ];
        foreach ($extra as $k => $v) {
            if (!is_null($v) && $v !== '') $data[$k] = $v;
        }

        // Normalisasi tempat_lahir
        if (isset($data['tempat_lahir'])) {
            $data['tempat_lahir'] = trim((string)$data['tempat_lahir']) ?: null;
        }

        // tgl_lahir (nullable → YYYY-MM-DD)
        if (!empty($data['tgl_lahir'])) {
            try {
                $dt = \Carbon\Carbon::parse($data['tgl_lahir']);
                $data['tgl_lahir'] = $dt->toDateString();
            } catch (\Throwable $e) {
                $data['tgl_lahir'] = null;
            }
        }

        // Handle file multipart (attachment_ktp, attachment_bukti)
        // Aliases dari form statis: upload_ktp -> attachment_ktp, attachment -> attachment_bukti
        if ($request->hasFile('upload_ktp')) {
            $data['attachment_ktp'] = $request->file('upload_ktp')->store('tickets/ktp', 'public');
        }
        if ($request->hasFile('attachment')) {
            $data['attachment_bukti'] = $request->file('attachment')->store('tickets/bukti', 'public');
        }

        // Tetap dukung nama field "attachment_ktp" dan "attachment_bukti" jika dikirim sesuai
        if ($request->hasFile('attachment_ktp')) {
            $data['attachment_ktp'] = $request->file('attachment_ktp')->store('tickets/ktp', 'public');
        }
        if ($request->hasFile('attachment_bukti')) {
            $data['attachment_bukti'] = $request->file('attachment_bukti')->store('tickets/bukti', 'public');
        }

        // is_nasabah: utamakan reporter_type; jika tidak ada, ambil boolean dari request
        if (isset($data['reporter_type'])) {
            $data['is_nasabah'] = ($data['reporter_type'] === 'nasabah');
        } elseif ($request->has('is_nasabah')) {
            $data['is_nasabah'] = $request->boolean('is_nasabah');
        }

        // Generate nomor tiket
        $ticketNo = $this->generateTicketNo();

        // Filter payload ke kolom yang diizinkan (fillable)
        $fillable = (new \App\Models\Ticket())->getFillable();
        $payload  = array_intersect_key(array_merge($data, ['ticket_no' => $ticketNo]), array_flip($fillable));

        // Simpan dalam transaksi untuk keamanan dan sekaligus buat event history
        try {
            $ticket = DB::transaction(function () use ($payload) {
                $t = Ticket::create($payload);

                // record event 'created' jika model TicketEvent ada
                if (class_exists(TicketEvent::class)) {
                    TicketEvent::create([
                        'ticket_id' => $t->id,
                        'type'      => 'created',
                        'meta'      => json_encode([
                            'ticket_no' => $t->ticket_no,
                            'title'     => $t->title,
                            'reporter'  => $t->reporter_name ?? null,
                        ]),
                        'user_id'   => auth()->id() ?? null,
                    ]);
                }

                return $t;
            });

            // Email ke reporter saat tiket dibuat (API) pakai template view
            try {
                if (!empty($ticket->email)) {
                    \Illuminate\Support\Facades\Mail::send('emails.ticket', [
                        'kind'          => 'created',
                        'ticket_no'     => $ticket->ticket_no,
                        'title'         => $ticket->title,
                        'reporter_name' => $ticket->reporter_name,
                        'status'        => $ticket->status,
                        'actionText'    => 'Lihat Tiket',
                        'actionUrl'     => '', // hide button for reporter
                    ], function ($m) use ($ticket) {
                        $m->to($ticket->email)->subject('[' . config('app.name') . '] Tiket Dibuat: ' . $ticket->ticket_no);
                    });
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('api_mail_open_failed', ['ticket_id' => $ticket->id, 'error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket created',
                'data' => $ticket,
            ], 201);
        } catch (\Throwable $e) {
            // Gagal koneksi DB atau query lainnya: beri respons error terstruktur
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a unique ticket number.
     * Format: TCK-YYYYMMDD-XXXXXX
     */
    protected function generateTicketNo(): string
    {
        $date = now()->format('Ymd');
        // coba cek unik; jika koneksi DB gagal, fallback acak
        try {
            do {
                $rand = strtoupper(Str::random(6)); // alphanumeric 6 chars
                $ticketNo = "TCK-{$date}-{$rand}";
            } while (Ticket::where('ticket_no', $ticketNo)->exists());
            return $ticketNo;
        } catch (\Throwable $e) {
            // fallback tanpa cek DB
            return "TCK-{$date}-" . strtoupper(Str::random(6));
        }
    }

    /**
     * Show ticket by ticket_no
     */
    public function showByTicketNo($ticket_no): JsonResponse
    {
        $ticket = Ticket::where('ticket_no', $ticket_no)->first();

        if (! $ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket not found'], 404);
        }

        // Simplified payload
        $payload = [
            'ticket_no'     => $ticket->ticket_no,
            'status'        => $ticket->status,
            'reporter_name' => $ticket->reporter_name,
            'category'      => $ticket->category,
            'kode_kantor'   => $ticket->kode_kantor,
            'created_at'    => optional($ticket->created_at)->toDateTimeString(),
            'updated_at'    => optional($ticket->updated_at)->toDateTimeString(),
            // always include these fields (can be null)
            'title'         => $ticket->title ?? null,
            'detail'        => $ticket->detail ?? null,
            'tindak_lanjut' => $ticket->tindak_lanjut ?? null,
        ];

        // debug: log what we send to help verify live payload
        \Illuminate\Support\Facades\Log::info('api_show_ticket_payload', [
            'ticket_no' => $ticket_no,
            'payload_keys' => array_keys($payload),
        ]);

        return response()->json(['success' => true, 'data' => $payload]);
    }
}
