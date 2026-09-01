<?php

namespace App\Livewire\Admin\BerberApp\Barbers;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Domain\BerberApp\Barber\Actions\CreateBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class Create extends Component
{
        use WithPagination, WithFileUploads;
     public $name = '';
    public $specialization = '';
    public $phone = '';
    public $commission_rate = '';
    public $photo = '';

    public function render() {
        abort_if_cannot('add_barbers');
        return view('livewire.admin.berber-app.barbers.create', [
        ])->layout('components.layouts.app')->title(__('barbers.Add Barber'));
    }
    public function store(CreateBarberAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = $this->photo->store('uploads/barbers', 'uploads'); }
 $dto = BarberDTO::fromArray([
            'name' => $this->name,
            'specialization' => $this->specialization,
            'phone' => $this->phone,
            'commission_rate' => $this->commission_rate,
            'photo' => $this->photo,
        ]); $action->execute($dto); session()->flash('success', __('barbers.created')); return to_route('admin.barbers.index'); }
    protected function rules(): array { return Barber::rules(); }
}
