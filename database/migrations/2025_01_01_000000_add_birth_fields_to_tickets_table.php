<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ...existing columns...
            $table->string('tempat_lahir')->nullable()->after('alamat');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // ...existing columns...
            $table->dropColumn(['tempat_lahir', 'tgl_lahir']);
        });
    }
};
