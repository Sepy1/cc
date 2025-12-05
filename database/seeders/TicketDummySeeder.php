<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketDummySeeder extends Seeder
{
    public function run(): void
    {
        // Tiket Nasabah
        Ticket::create([
            'ticket_no'       => 'TCK-' . strtoupper(Str::random(6)),
            'title'           => 'Permasalahan transaksi gagal',
            'detail'          => "Transaksi di ATM gagal tetapi saldo terdebet.\nMohon bantuan verifikasi.",
            'category'        => 'Transaksi',
            'status'          => 'pending',
            'reporter_name'   => 'Budi Nasabah',
            'email'           => 'budi.nasabah@example.com',
            'phone'           => '081234567890',
            'reporter_type'   => 'nasabah',
            'is_nasabah'      => true,
            'id_ktp'          => '3174XXXXXXXX1234',
            'nomor_rekening'  => '1234567890',
            'nama_ibu'        => 'Siti',
            'alamat'          => "Jl. Melati No. 12\nJakarta",
            'kode_kantor'     => 'JKT001',
            'media_closing'   => 'whatsapp',
            'attachment_ktp'  => null,
            'attachment_bukti'=> null,
            'created_at'      => Carbon::now()->subDays(2),
            'updated_at'      => Carbon::now()->subDay(),
        ]);

        // Tiket Umum (non-nasabah)
        Ticket::create([
            'ticket_no'       => 'TCK-' . strtoupper(Str::random(6)),
            'title'           => 'Pertanyaan layanan kantor',
            'detail'          => "Ingin konfirmasi jam operasional dan layanan penggantian kartu.",
            'category'        => 'Informasi',
            'status'          => 'open',
            'reporter_name'   => 'Sari Umum',
            'email'           => 'sari.umum@example.com',
            'phone'           => '082233445566',
            'reporter_type'   => 'umum',
            'is_nasabah'      => false,
            'id_ktp'          => null,
            'nomor_rekening'  => null,
            'nama_ibu'        => null,
            'alamat'          => null,
            'kode_kantor'     => null,
            'media_closing'   => 'telepon',
            'attachment_ktp'  => null,
            'attachment_bukti'=> null,
            'created_at'      => Carbon::now()->subDay(),
            'updated_at'      => Carbon::now(),
        ]);
    }
}