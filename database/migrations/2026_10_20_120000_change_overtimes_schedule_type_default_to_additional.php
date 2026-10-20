<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('overtimes', 'schedule_type')) {
            return;
        }

        DB::statement("ALTER TABLE overtimes MODIFY schedule_type ENUM('piket', 'additional') NOT NULL DEFAULT 'additional'");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('overtimes', 'schedule_type')) {
            return;
        }

        DB::statement("ALTER TABLE overtimes MODIFY schedule_type ENUM('piket', 'additional') NOT NULL DEFAULT 'piket'");
    }
};
