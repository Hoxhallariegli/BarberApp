<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Trip;

$trip = Trip::latest()->first();
if ($trip) {
    echo "ID: " . $trip->id . "\n";
    echo "Start Lat: " . ($trip->start_lat ?? 'NULL') . "\n";
    echo "Start Lng: " . ($trip->start_lng ?? 'NULL') . "\n";
    echo "Start Time: " . ($trip->start_time ? $trip->start_time->toDateTimeString() : 'NULL') . "\n";
    echo "Status: " . $trip->status . "\n";
} else {
    echo "No trip found.\n";
}
