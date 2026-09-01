<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('call_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->enum('status', ['pending', 'processing', 'started', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('device_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_jobs');
    }
};
