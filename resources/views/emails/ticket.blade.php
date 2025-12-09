@php
    $appName = config('app.name', 'Aplikasi');
    $kind = ($kind ?? 'generic');
    $ticketNo = $ticket_no ?? ($ticket?->ticket_no ?? null);
    $ticketTitle = $title ?? ($ticket?->title ?? null);
    $reporter = $reporter_name ?? ($ticket?->reporter_name ?? null);
    $status = $status ?? ($ticket?->status ?? null);
    $old = $old_status ?? ($changes['status']['from'] ?? null);
    $new = $new_status ?? ($changes['status']['to'] ?? $status);
    $assignee = $assignee_name ?? ($assigned_to_name ?? null);
    $tindakLanjut = $tindak_lanjut ?? ($ticket?->tindak_lanjut ?? null);

    // Headline & intro otomatis sesuai 'kind'
    switch ($kind) {
        case 'created':
            $headline = 'Tiket Anda Berhasil Dibuat';
            $intro = 'Terima kasih, tiket Anda telah kami terima dan sedang diproses.';
            break;
        case 'status_changed':
            $headline = 'Status Tiket Diperbarui';
            $intro = 'Ada pembaruan status pada tiket Anda.';
            break;
        case 'assigned':
            $headline = 'Tiket Baru Di-assign';
            $intro = 'Anda menerima penugasan tiket baru.';
            break;
        default:
            $headline = $headline ?? 'Notifikasi Tiket';
            $intro = $intro ?? 'Berikut adalah informasi tiket Anda.';
            break;
    }

    $actionText = $actionText ?? 'Lihat Tiket';
    $actionUrl  = $actionUrl  ?? url('/'); // sesuaikan pada pemanggilan
    $footerNote = $footerNote ?? "Email ini dikirim otomatis oleh {$appName}. Mohon tidak membalas email ini.";
    $logoUrl    = $logoUrl ?? url('images/logo.png'); // jika ada

    // Prevent duplicate app name in the HTML <title>
    // If $headline already contains the app name, use $headline as-is.
    // Otherwise append " — AppName"
    $normalizedHeadline = trim((string) $headline);
    $titleTag = stripos($normalizedHeadline, $appName) !== false
        ? $normalizedHeadline
        : ($normalizedHeadline . ' — ' . $appName);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $titleTag }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
                    <tr>
                        <td style="padding:18px 22px;background:#111827;color:#fff;">
                            <table width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="left" style="font-size:16px;font-weight:bold;">
                                        @if($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $appName }}" style="height:28px;vertical-align:middle;margin-right:8px;">
@else
    <span style="vertical-align:middle">{{ $appName }}</span>
@endif

                                    </td>
                                    <td align="right" style="font-size:12px;color:#cbd5e1;">
                                        {{ now()->format('d M Y H:i') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px;">
                            <h1 style="margin:0 0 8px;font-size:18px;line-height:1.35;color:#111827;">{{ $headline }}</h1>
                            <p style="margin:0 0 16px;color:#374151;font-size:14px;line-height:1.6;">
                                {{ $intro }}
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;margin:10px 0 16px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        @if($ticketNo)
                                            <p style="margin:0 0 8px;font-size:14px;color:#111;"><strong>No. Tiket:</strong> {{ $ticketNo }}</p>
                                        @endif
                                        @if($ticketTitle)
                                            <p style="margin:0 0 8px;font-size:14px;color:#111;"><strong>Judul:</strong> {{ $ticketTitle }}</p>
                                        @endif
                                        @if($reporter)
                                            <p style="margin:0 0 8px;font-size:14px;color:#111;"><strong>Pelapor:</strong> {{ $reporter }}</p>
                                        @endif

                                        @if($kind === 'status_changed')
                                            <p style="margin:6px 0 0;font-size:14px;color:#111;">
                                                <strong>Status:</strong>
                                                @if($old)<span style="color:#6b7280">{{ ucfirst((string)$old) }}</span> → @endif
                                                <span style="color:#111827">{{ ucfirst((string)$new) }}</span>
                                            </p>
                                        @elseif($kind === 'created' && $status)
                                            <p style="margin:6px 0 0;font-size:14px;color:#111;">
                                                <strong>Status:</strong> {{ ucfirst((string)$status) }}
                                            </p>
                                        @elseif($kind === 'assigned' && $assignee)
                                            <p style="margin:6px 0 0;font-size:14px;color:#111;">
                                                <strong>Ditugaskan kepada:</strong> {{ $assignee }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @isset($lines)
                                @foreach((array)$lines as $line)
                                    <p style="margin:0 0 10px;color:#374151;font-size:14px;line-height:1.6;">{{ $line }}</p>
                                @endforeach
                            @endisset

                            @if(!empty($actionUrl))
                                <div style="margin:18px 0 6px;">
                                    <a href="{{ $actionUrl }}" target="_blank"
                                       style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;font-size:14px;">
                                        {{ $actionText }}
                                    </a>
                                </div>
                            @endif

                            {{-- Tindak Lanjut (shown when provided) --}}
                            @if(!empty($tindakLanjut))
                                <div style="margin:14px 0 0;">
                                    <div style="font-size:14px;color:#111827;font-weight:bold;margin-bottom:6px;">Tindak Lanjut</div>
                                    <pre style="margin:0;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px;white-space:pre-wrap;color:#374151;font-size:14px;line-height:1.6;">{{ $tindakLanjut }}</pre>
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:14px 22px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;color:#6b7280;font-size:12px;line-height:1.6;">{{ $footerNote }}</p>
                        </td>
                    </tr>
                </table>

                <div style="color:#9ca3af;font-size:11px;margin-top:10px;">
                    © {{ date('Y') }} {{ $appName }}. All rights reserved.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
