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
    .sign { width: 32%; display: inline-block; text-align: center; }
</style>
</head>
<body>
    <div class="header">
        <div>PT BPR BKK JAWA TENGAH (Perseroda)</div>
        <div class="muted">Laporan Bulanan Call Center</div>
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
                <th>Jenis Aduan</th>
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
                    {{ \Illuminate\Support\Str::limit(trim($t->detail ?? $t->title ?? '-'), 120) }}
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

    <div class="mt-20">
        <div class="sign">
            <div>Dibuat</div>
            <div style="height:64px;"></div>
            <div class="small"><u>CSA</u></div>
        </div>
        <div class="sign">
            <div>Diperiksa</div>
            <div style="height:64px;"></div>
            <div class="small"><u>Kepala Bidang Sekretaris</u><br>Perusahaan &amp; Humas</div>
        </div>
        <div class="sign">
            <div>Disetujui</div>
            <div style="height:64px;"></div>
            <div class="small"><u>Sekretaris Perusahaan</u></div>
        </div>
    </div>
</body>
</html>
