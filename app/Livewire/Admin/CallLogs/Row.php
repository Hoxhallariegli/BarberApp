<?php

namespace App\Livewire\Admin\CallLogs;

use App\Models\CallLog;
use Livewire\Component;

class Row extends Component { public CallLog $item; public function render() { return view('livewire.admin.call-logs.row'); } }