<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up() { Schema::create('ba_device_tokens', function (Blueprint $table) { $table->id();
            $table->integer('user_id')->nullable();
            $table->string('fcm_token');
            $table->string('device_type')->nullable();
            $table->timestamps(); }); } public function down() { Schema::dropIfExists('ba_device_tokens'); } };