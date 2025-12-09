<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Bulanan Call Center</title>
<style>
    @page { margin: 24mm 18mm; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 6px; }
    .muted { color:#555; }
    .header { margin-bottom: 14px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 6px 8px; vertical-align: top; }
    th { text-align: center; background: #f4f4f4; font-weight: 700; }
    .no-border td { border: none; }
    .small { font-size: 11px; }
    .nowrap { white-space: nowrap; }
    .center { text-align: center; }
    .right { text-align: right; }
    .mt-8 { margin-top: 8px; }
    .mt-12 { margin-top: 12px; }
    .mt-20 { margin-top: 20px; }
    .brand { display:flex; align-items:center; gap:12px; }
    .brand img { height:100px; }
    /* Hindari pecah halaman pada blok tanda tangan */
    .sign-wrap { display: block; page-break-inside: avoid; margin-top: 16px; }
    .sign { width: 32%; display: inline-block; text-align: center; vertical-align: top; }
    /* Sedikit kurangi jarak agar muat satu halaman */
    .sign .space { height: 48px; }
</style>
</head>
<body>
    @php
        // Build a safe logo src for Dompdf
        $logoPath = public_path('images/logo.png');
        $logoSrc = null;
        if (file_exists($logoPath) && is_readable($logoPath) && extension_loaded('gd')) {
            // preferred: data URL (note the comma after base64)
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            // fallback: file:// absolute path if needed
            if (!$logoSrc) {
                $real = realpath($logoPath);
                $logoSrc = $real ? ('file://' . $real) : null;
            }
        }
    @endphp
    <div class="header">
        <div class="brand">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo">
            @endif
            <div>
                
                <div class="muted">Laporan Bulanan Call Center</div>
            </div>
        </div>
        <div class="mt-8">
            Bulan: <strong>{{ $bulanNama }}</strong><br>
            Tahun: <strong>{{ $tahunNama }}</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">No</th>
                <th class="nowrap" style="width:85px">Tanggal</th>
                <th>Nama Pelapor</th>
                <th>Pokok Aduan</th>
                <th>Unit Terkait</th>
                <th style="width:120px">Status Penyelesaian</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
        @php
            $selesaiStatuses = ['resolved','closed'];
        @endphp
        @forelse($tickets as $i => $t)
            <tr>
                <td class="center">{{ $i+1 }}</td>
                <td class="nowrap">{{ optional($t->created_at)->format('d/m/Y') }}</td>
                <td>{{ $t->reporter_name ?? '-' }}</td>
                <td>{{ $t->category ?? '-' }}</td>
                <td>{{ optional($t->assignedTo)->name ?? '-' }}</td>
                <td class="center">{{ in_array($t->status, $selesaiStatuses) ? 'Selesai' : 'Proses' }}</td>
                <td class="small">
                    @php
                        $dur = '-';
                        if (in_array($t->status, $selesaiStatuses) && $t->created_at && $t->closing_at && $t->closing_at->gte($t->created_at)) {
                            $hours = $t->closing_at->diffInHours($t->created_at);
                            $dur = $hours . ' jam (≈ ' . number_format($hours/24, 1) . ' hari)';
                        }
                    @endphp
                    {{ $dur }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="center">Tidak ada data pada periode ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-12"><strong>Rekapitulasi Bulanan:</strong></div>
    <table class="mt-8">
        <tbody>
            <tr>
                <td style="width:360px;border:1px solid #333;">1. Jumlah Pengaduan Diterima</td>
                <td class="right" style="border:1px solid #333;">: {{ $totalDiterima }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #333;">2. Jumlah Pengaduan Selesai</td>
                <td class="right" style="border:1px solid #333;">: {{ $totalSelesai }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #333;">3. Jumlah Pengaduan Dalam Proses</td>
                <td class="right" style="border:1px solid #333;">: {{ $totalDalamProses }}</td>
            </tr>
            <tr>
                <td style="border:1px solid #333;">4. Rata-rata Waktu Penyelesaian</td>
                <td class="right" style="border:1px solid #333;">: {{ $avgHours }} jam (≈ {{ number_format($avgHours/24,1) }} hari)</td>
            </tr>
        </tbody>
    </table>

    <div class="sign-wrap">
        <div class="sign">
            <div>Dibuat</div>
            <div class="space"></div>
            <div class="space"></div>
            <div class="small"><u>AJI APRIAN FIRMANSYAH, SE</u><br>CSA</br></div>
        </div>
        <div class="sign">
            <div>Diperiksa</div>
            <div class="space"></div>
            <div class="space"></div>
            <div class="small"><u>PUTRI SABATI DAMARISTI, SE</u><br>KEPALA BIDANG SEKRETARIS PERUSAHAAN DAN HUMAS</div>
        </div>
        <div class="sign">
            <div>Disetujui</div>
            <div class="space"></div>
            <div class="space"></div>
            <div class="small"><u>FANDY FARISSA, SH., MK.N</u><br>SEKRETARIS PERUSAHAAN</div>
        </div>
    </div>
</body>
</html>
