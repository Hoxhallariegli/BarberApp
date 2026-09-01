<?php

namespace App\Livewire\Admin\BerberApp\Reminders;

use App\Models\BerberApp\Reminder;
use Livewire\Component;

class Row extends Component { public Reminder $item; public function render() { return view('livewire.admin.berber-app.reminders.row'); } }