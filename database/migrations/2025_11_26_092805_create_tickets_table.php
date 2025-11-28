<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_no')->unique()->index(); // nomor tiket yang digenerate
            $table->string('reporter_name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('category')->nullable();
            $table->string('title');
            $table->text('detail');
            $table->string('status')->default('open'); // contoh: open, pending, closed
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
