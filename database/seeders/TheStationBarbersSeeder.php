<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Customer;
use App\Models\BerberApp\Service;
use App\Models\BerberApp\BarberSchedule;
use App\Models\BerberApp\Booking;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TheStationBarbersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Permissions for Barber App
        $modules = [
            'Barbers' => ['view_barbers', 'add_barbers', 'edit_barbers', 'delete_barbers'],
            'Bookings' => ['view_bookings', 'add_bookings', 'edit_bookings', 'delete_bookings'],
            'Customers' => ['view_customers', 'add_customers', 'edit_customers', 'delete_customers'],
            'Services' => ['view_services', 'add_services', 'edit_services', 'delete_services'],
            'Payments' => ['view_payments', 'add_payments', 'edit_payments', 'delete_payments'],
            'Reminders' => ['view_reminders', 'add_reminders', 'edit_reminders', 'delete_reminders'],
            'SMS' => ['view_sms_settings', 'view_sms_logs', 'view_sms_devices', 'view_sms_templates'],
        ];

        foreach ($modules as $module => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(
                    ['name' => $perm],
                    ['label' => ucwords(str_replace('_', ' ', $perm)), 'module' => $module]
                );
            }
        }

        // 2. Create Admin Role and Sync Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // 3. Create the Admin User
        $adminUser = User::updateOrCreate(
            ['email' => 'Admin1@admin.admin'],
            [
                'name' => 'Admin The Station',
                'password' => Hash::make('Admin1@admin.admin'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $adminUser->assignRole($adminRole);

        // 3. Create Services (Multi-language)
        $services = [
            [
                'name' => ['en' => 'Classic Haircut', 'sq' => 'Prerje Flokësh Klasike'],
                'price' => 500,
                'duration_minutes' => 30
            ],
            [
                'name' => ['en' => 'Beard Trim', 'sq' => 'Rregullim Mjekre'],
                'price' => 300,
                'duration_minutes' => 20
            ],
            [
                'name' => ['en' => 'Hair & Beard', 'sq' => 'Qethje & Mjekër'],
                'price' => 700,
                'duration_minutes' => 45
            ],
            [
                'name' => ['en' => 'Royal Shave', 'sq' => 'Rrojë Mbretërore'],
                'price' => 600,
                'duration_minutes' => 30
            ],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['price' => $s['price'], 'duration_minutes' => $s['duration_minutes']], [
                'name' => $s['name']
            ]);
        }

        $allServices = Service::all();

        // 4. Create Barbers
        $barberNames = ['Artani', 'Bledi', 'Erioni'];
        foreach ($barberNames as $index => $name) {
            $barber = Barber::updateOrCreate(['name' => $name], [
                'specialization' => 'Master Barber',
                'phone' => '06900000' . ($index + 1),
                'commission_rate' => 15,
                'active' => true,
            ]);

            // Create Schedules for each barber
            for ($day = 1; $day <= 6; $day++) { // Monday to Saturday
                BarberSchedule::updateOrCreate([
                    'barber_id' => $barber->id,
                    'day_of_week' => $day
                ], [
                    'start_time' => '09:00:00',
                    'end_time' => '19:00:00',
                    'break_start_time' => '13:00:00',
                    'break_end_time' => '14:00:00',
                    'is_working' => true
                ]);
            }
        }

        $allBarbers = Barber::all();

        // 5. Create some Customers
        $customers = [
            ['name' => 'Egli Test', 'phone' => '0691111111', 'email' => 'egli@test.com'],
            ['name' => 'Joni Test', 'phone' => '0692222222', 'email' => 'joni@test.com'],
        ];

        foreach ($customers as $c) {
            Customer::updateOrCreate(['phone' => $c['phone']], $c);
        }

        $allCustomers = Customer::all();

        // 6. Create some Bookings
        if ($allBarbers->count() > 0 && $allCustomers->count() > 0 && $allServices->count() > 0) {
            Booking::create([
                'customer_id' => $allCustomers->first()->id,
                'barber_id' => $allBarbers->first()->id,
                'service_id' => $allServices->first()->id,
                'customer_name' => $allCustomers->first()->name,
                'customer_phone' => $allCustomers->first()->phone,
                'appointment_datetime' => Carbon::now()->addDays(1)->setHour(10)->setMinute(0),
                'status' => 'pending',
                'token' => \Illuminate\Support\Str::random(32),
            ]);
        }
    }
}
