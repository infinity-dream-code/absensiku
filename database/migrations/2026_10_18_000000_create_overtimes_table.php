<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'overtime_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
