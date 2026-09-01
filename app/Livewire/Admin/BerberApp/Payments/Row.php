<?php

namespace App\Livewire\Admin\BerberApp\Payments;

use App\Models\BerberApp\Payment;
use Livewire\Component;

class Row extends Component { public Payment $item; public function render() { return view('livewire.admin.berber-app.payments.row'); } }