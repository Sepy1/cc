<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_no')->unique();
                $table->string('reporter_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('category')->nullable();
                $table->string('title');
                $table->text('detail')->nullable();
                $table->string('status')->default('open');
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
                // kolom baru
                $table->string('tempat_lahir')->nullable();
                $table->date('tgl_lahir')->nullable();

                $table->timestamps();
            });
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('tickets', 'tgl_lahir')) {
                $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (Schema::hasColumn('tickets', 'tgl_lahir')) {
                    $table->dropColumn('tgl_lahir');
                }
                if (Schema::hasColumn('tickets', 'tempat_lahir')) {
                    $table->dropColumn('tempat_lahir');
                }
            });
        }
    }
};
