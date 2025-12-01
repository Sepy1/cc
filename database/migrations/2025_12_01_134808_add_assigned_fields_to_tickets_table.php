<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            }
            if (!Schema::hasColumn('tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
            if (Schema::hasColumn('tickets', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to'); // Laravel 9+
            }
        });
    }
};