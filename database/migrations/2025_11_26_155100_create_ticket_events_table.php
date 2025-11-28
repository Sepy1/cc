<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->string('type'); // contoh: created, assigned, status_change, replied, updated, deleted
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // actor
            $table->json('meta')->nullable(); // menyimpan data tambahan (who, from/to, note, officer_id, dll)
            $table->timestamps(); // created_at => waktu event
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');
    }
};
