<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ba_services', function (Blueprint $table) {
            $table->renameColumn('duration', 'duration_minutes');
        });
    }

    public function down()
    {
        Schema::table('ba_services', function (Blueprint $table) {
            $table->renameColumn('duration_minutes', 'duration');
        });
    }
};
