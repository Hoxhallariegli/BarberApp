<?php

namespace App\Livewire\Admin\BerberApp\Barbers;

use App\Models\BerberApp\Barber;
use Livewire\Component;

class Row extends Component { public Barber $item; public function render() { return view('livewire.admin.berber-app.barbers.row'); } }