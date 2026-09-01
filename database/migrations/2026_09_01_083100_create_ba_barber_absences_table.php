<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ba_barber_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_id')->constrained('ba_barbers')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time')->nullable(); // If null, means the whole day
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ba_barber_absences');
    }
};
