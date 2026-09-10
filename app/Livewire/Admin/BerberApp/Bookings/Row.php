<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use Livewire\Component;

use Livewire\Attributes\On;

class Row extends Component
{
    public Booking $item;

    #[On('refresh-bookings')]
    public function refresh()
    {
        $this->item->refresh();
    }

    public function render() { return view('livewire.admin.berber-app.bookings.row'); }
}
