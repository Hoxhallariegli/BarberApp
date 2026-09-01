<?php

namespace App\Livewire\Admin\BerberApp\Customers;

use App\Models\BerberApp\Customer;
use Livewire\Component;

class Row extends Component { public Customer $item; public function render() { return view('livewire.admin.berber-app.customers.row'); } }