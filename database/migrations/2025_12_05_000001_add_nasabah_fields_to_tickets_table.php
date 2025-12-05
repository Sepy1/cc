<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ...existing columns...
            $table->string('reporter_type')->nullable()->index(); // 'nasabah' atau 'umum'
            $table->boolean('is_nasabah')->nullable();            // fallback flag

            $table->string('id_ktp')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kode_kantor')->nullable();

            $table->string('attachment_ktp')->nullable();
            $table->string('attachment_bukti')->nullable();

            $table->string('media_closing')->nullable(); // whatsapp, telepon, offline
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ...existing columns...
            $table->dropColumn([
                'reporter_type',
                'is_nasabah',
                'id_ktp',
                'nomor_rekening',
                'nama_ibu',
                'alamat',
                'kode_kantor',
                'attachment_ktp',
                'attachment_bukti',
                'media_closing',
            ]);
        });
    }
};
