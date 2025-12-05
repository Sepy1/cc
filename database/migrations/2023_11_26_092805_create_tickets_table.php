<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_no')->unique();
                $table->string('reporter_name');
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->string('category')->nullable();
                $table->string('title');
                $table->text('detail')->nullable();
                $table->string('status')->default('open');

                // kolom tambahan sesuai model & controller
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('assigned_at')->nullable();
                $table->text('tindak_lanjut')->nullable();
                $table->string('reporter_type')->nullable();
                $table->boolean('is_nasabah')->default(false);
                $table->string('id_ktp')->nullable();
                $table->string('nomor_rekening')->nullable();
                $table->string('nama_ibu')->nullable();
                $table->text('alamat')->nullable();
                $table->string('kode_kantor')->nullable();
                $table->string('attachment_ktp')->nullable();
                $table->string('attachment_bukti')->nullable();
                $table->string('media_closing')->nullable();
                $table->timestamp('closing_at')->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->date('tgl_lahir')->nullable();

                $table->timestamps();
            });

            return;
        }

        // Jika tabel sudah ada: jangan tambah kolom apa pun di sini
        // Kolom tambahan akan diabaikan untuk mencegah duplikasi.
    }

    public function down(): void
    {
        // Aman: hanya drop jika memang dibuat oleh migration ini dan tidak dipakai
        // Biasanya di production tidak melakukan drop table.
        // Jika tetap ingin rollback, comment baris di bawah:
        // Schema::dropIfExists('tickets');
    }
};
