<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use Livewire\Component;

class Row extends Component { public Booking $item; public function render() { return view('livewire.admin.berber-app.bookings.row'); } }