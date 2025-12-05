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

        // Jika tabel sudah ada: tambahkan kolom yang belum ada (idempotent)
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'ticket_no')) $table->string('ticket_no')->nullable()->after('id');
            if (!Schema::hasColumn('tickets', 'reporter_name')) $table->string('reporter_name')->nullable()->after('ticket_no');
            if (!Schema::hasColumn('tickets', 'phone')) $table->string('phone', 50)->nullable()->after('reporter_name');
            if (!Schema::hasColumn('tickets', 'email')) $table->string('email')->nullable()->after('phone');
            if (!Schema::hasColumn('tickets', 'category')) $table->string('category')->nullable()->after('email');
            if (!Schema::hasColumn('tickets', 'title')) $table->string('title')->nullable()->after('category');
            if (!Schema::hasColumn('tickets', 'detail')) $table->text('detail')->nullable()->after('title');
            if (!Schema::hasColumn('tickets', 'status')) $table->string('status')->default('open')->after('detail');

            if (!Schema::hasColumn('tickets', 'assigned_to')) $table->unsignedBigInteger('assigned_to')->nullable()->after('status');
            if (!Schema::hasColumn('tickets', 'assigned_at')) $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            if (!Schema::hasColumn('tickets', 'tindak_lanjut')) $table->text('tindak_lanjut')->nullable()->after('assigned_at');
            if (!Schema::hasColumn('tickets', 'reporter_type')) $table->string('reporter_type')->nullable()->after('tindak_lanjut');
            if (!Schema::hasColumn('tickets', 'is_nasabah')) $table->boolean('is_nasabah')->default(false)->after('reporter_type');
            if (!Schema::hasColumn('tickets', 'id_ktp')) $table->string('id_ktp')->nullable()->after('is_nasabah');
            if (!Schema::hasColumn('tickets', 'nomor_rekening')) $table->string('nomor_rekening')->nullable()->after('id_ktp');
            if (!Schema::hasColumn('tickets', 'nama_ibu')) $table->string('nama_ibu')->nullable()->after('nomor_rekening');
            if (!Schema::hasColumn('tickets', 'alamat')) $table->text('alamat')->nullable()->after('nama_ibu');
            if (!Schema::hasColumn('tickets', 'kode_kantor')) $table->string('kode_kantor')->nullable()->after('alamat');
            if (!Schema::hasColumn('tickets', 'attachment_ktp')) $table->string('attachment_ktp')->nullable()->after('kode_kantor');
            if (!Schema::hasColumn('tickets', 'attachment_bukti')) $table->string('attachment_bukti')->nullable()->after('attachment_ktp');
            if (!Schema::hasColumn('tickets', 'media_closing')) $table->string('media_closing')->nullable()->after('attachment_bukti');
            if (!Schema::hasColumn('tickets', 'closing_at')) $table->timestamp('closing_at')->nullable()->after('media_closing');
            if (!Schema::hasColumn('tickets', 'tempat_lahir')) $table->string('tempat_lahir')->nullable()->after('alamat');
            if (!Schema::hasColumn('tickets', 'tgl_lahir')) $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
        });
    }

    public function down(): void
    {
        // Aman: hanya drop jika memang dibuat oleh migration ini dan tidak dipakai
        // Biasanya di production tidak melakukan drop table.
        // Jika tetap ingin rollback, comment baris di bawah:
        // Schema::dropIfExists('tickets');
    }
};
