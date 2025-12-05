<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketRandomSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        $categories = ['Transaksi', 'Informasi', 'Akun', 'ATM', 'Kartu', 'Layanan', 'Mobile Banking'];
        $statuses   = ['open', 'pending', 'resolved', 'rejected'];

        for ($i = 0; $i < 100; $i++) {
            $isNasabah = $faker->boolean(60); // ~60% nasabah
            $reporterType = $isNasabah ? 'nasabah' : 'umum';

            $created = Carbon::now()->subDays($faker->numberBetween(0, 20))->subMinutes($faker->numberBetween(0, 1440));
            $updated = (clone $created)->addHours($faker->numberBetween(1, 72));

            $data = [
                'ticket_no'       => 'TCK-' . strtoupper(Str::random(6)),
                'title'           => $faker->sentence($faker->numberBetween(3, 6)),
                'detail'          => $faker->paragraph($faker->numberBetween(1, 3)),
                'category'        => $faker->randomElement($categories),
                'status'          => $faker->randomElement($statuses),

                'reporter_name'   => $faker->name(),
                'email'           => $faker->optional(0.8)->safeEmail(),
                'phone'           => '0' . $faker->numberBetween(81200000000, 85999999999),

                'reporter_type'   => $reporterType,
                'is_nasabah'      => $isNasabah,

                // Field nasabah (nullable random jika nasabah)
                'id_ktp'          => $isNasabah ? $faker->optional(0.9)->numerify(str_repeat('#', 16)) : null,
                'nomor_rekening'  => $isNasabah ? $faker->optional(0.85)->numerify('##########') : null,
                'nama_ibu'        => $isNasabah ? $faker->optional(0.7)->firstName() : null,
                'alamat'          => $isNasabah ? $faker->optional(0.8)->address() : null,
                'kode_kantor'     => $isNasabah ? $faker->optional(0.6)->regexify('[A-Z]{3}[0-9]{3}') : null,

                'media_closing'   => $faker->optional(0.5)->randomElement(['whatsapp', 'telepon', 'offline']),

                // Lampiran dummy (null)
                'attachment_ktp'  => null,
                'attachment_bukti'=> null,

                'created_at'      => $created,
                'updated_at'      => $updated,
            ];

            Ticket::create($data);
        }
    }
}