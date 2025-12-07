<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function list(Request $request)
    {
        $userId = auth()->id();
        $items = Notification::with(['ticket'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'count_unseen' => $items->whereNull('seen_at')->count(),
            'items' => $items->map(function ($n) {
                $meta = $n->meta ?? [];
                $isStatus = $n->type === 'status_changed';
                $isAssigned = $n->type === 'assigned';
                $ticketNo = optional($n->ticket)->ticket_no ?? ('#'.$n->ticket_id);
                $label = $isStatus
                    ? ('Status → ' . ($meta['to'] ?? ($meta['status'] ?? '')))
                    : ($isAssigned ? ('Tiket di-assign ke ' . ($meta['assigned_to_name'] ?? ('User #' . ($meta['assigned_to'] ?? '-')))) : 'Komentar baru');
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'seen' => !is_null($n->seen_at),
                    'created_at_human' => optional($n->created_at)->diffForHumans(),
                    'label' => $label,
                    'ticket_id' => $n->ticket_id,
                    'ticket_no' => $ticketNo,
                    'snippet' => $meta['snippet'] ?? null,
                ];
            }),
        ]);
    }

    public function markSeen(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        if (is_null($notification->seen_at)) {
            $notification->seen_at = now();
            $notification->save();
        }
        return response()->json(['ok' => true, 'seen_at' => $notification->seen_at]);
    }
}
