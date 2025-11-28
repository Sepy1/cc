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

        // Generate ticket number: TCK-YYYYMMDD-<6 random alnum>
        $ticketNo = $this->generateTicketNo();

        // Simpan dalam transaksi untuk keamanan dan sekaligus buat event history
        $ticket = DB::transaction(function () use ($data, $ticketNo) {
            $t = Ticket::create(array_merge($data, [
                'ticket_no' => $ticketNo,
                'status' => $data['status'] ?? 'open',
            ]));

            // record event 'created' jika model TicketEvent ada
            if (class_exists(TicketEvent::class)) {
                TicketEvent::create([
                    'ticket_id' => $t->id,
                    'type'      => 'created',
                    'meta'      => json_encode([
                        'ticket_no' => $t->ticket_no,
                        'title'     => $t->title,
                        'reporter'  => $t->reporter_name ?? ($data['reporter_name'] ?? null),
                    ]),
                    // jika request diautentikasi, catat user_id; kalau tidak, biarkan null
                    'user_id'   => auth()->id() ?? null,
                ]);
            }

            return $t;
        });

        return response()->json([
            'success' => true,
            'message' => 'Ticket created',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Generate a unique ticket number.
     * Format: TCK-YYYYMMDD-XXXXXX
     */
    protected function generateTicketNo(): string
    {
        $date = now()->format('Ymd');
        // loop hingga unique (jarang perlu lebih dari sekali)
        do {
            $rand = strtoupper(Str::random(6)); // alphanumeric 6 chars
            $ticketNo = "TCK-{$date}-{$rand}";
        } while (Ticket::where('ticket_no', $ticketNo)->exists());

        return $ticketNo;
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

        return response()->json(['success' => true, 'data' => $ticket]);
    }
}
