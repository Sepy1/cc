<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAssignedToToTicketsTable extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->after('status')->index();

            // if you want foreign key constraint:
            // $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // drop foreign if added
            // $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }
}
