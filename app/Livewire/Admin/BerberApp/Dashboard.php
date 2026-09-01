<?php

namespace App\Livewire\Admin\BerberApp;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Service;
use Carbon\Carbon;

#[Title('Berber App Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.berber-app.dashboard', [
            'totalBookings' => Booking::count(),
            'totalBarbers' => Barber::count(),
            'totalServices' => Service::count(),
            'recentBookings' => Booking::with(['barber', 'service', 'customer'])->latest()->take(5)->get(),
        ]);
    }
}
