<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('call_logs', function (Blueprint $table) { $table->id();
            $table->string('phone_number');
            $table->string('caller_name')->nullable();
            $table->enum('type', ['incoming', 'missed', 'outgoing']);
            $table->datetime('call_time');
            $table->boolean('is_client');
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('call_logs'); } };