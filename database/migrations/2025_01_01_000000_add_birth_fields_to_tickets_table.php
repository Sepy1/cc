<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tickets')) return;
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'tempat_lahir')) {
                $table->string('tempat_lahir')->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('tickets', 'tgl_lahir')) {
                $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            }
        });
    }
    public function down(): void
    {
        if (!Schema::hasTable('tickets')) return;
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'tgl_lahir')) $table->dropColumn('tgl_lahir');
            if (Schema::hasColumn('tickets', 'tempat_lahir')) $table->dropColumn('tempat_lahir');
        });
    }
};
