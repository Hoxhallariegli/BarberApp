<?php

namespace App\Livewire\Admin\Trips;

use App\Models\Trip;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Trip Details')]
class Show extends Component
{
    public Trip $trip;

    public function mount(Trip $trip)
    {
        $this->trip = $trip->load(['driver', 'vehicle']);
    }

    public function render()
    {
        return view('livewire.admin.trips.show')->layout('components.layouts.app');
    }
}
