<?php

namespace App\Livewire\Admin\BerberApp\DeviceTokens;

use App\Models\BerberApp\DeviceToken;
use Livewire\Component;

class Row extends Component { public DeviceToken $item; public function render() { return view('livewire.admin.berber-app.device-tokens.row'); } }