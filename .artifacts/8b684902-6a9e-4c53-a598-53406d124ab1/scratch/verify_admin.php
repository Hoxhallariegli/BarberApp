<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

$admin = User::where('email', 'admin@fleettrack.com')->first();
if ($admin) {
    echo "Admin User: " . $admin->name . " (UUID: " . $admin->id . ")\n";
    echo "Roles: " . $admin->getRoleNames()->implode(', ') . "\n";
    echo "Has all permissions: " . ($admin->hasAllPermissions(Permission::all()) ? 'YES' : 'NO') . "\n";
    echo "Permission count: " . $admin->getAllPermissions()->count() . "\n";
} else {
    echo "Admin user not found.\n";
}

$trips = \App\Models\Trip::count();
$vehicles = \App\Models\Vehicle::count();
$settings = \App\Models\Setting::count();
echo "Trips seeded: $trips\n";
echo "Vehicles seeded: $vehicles\n";
echo "Settings seeded: $settings\n";
