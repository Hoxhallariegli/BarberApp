<?php

namespace App\Livewire\Admin\BerberApp\Services;

use App\Models\BerberApp\Service;
use Livewire\Component;

class Row extends Component { public Service $item; public function render() { return view('livewire.admin.berber-app.services.row'); } }