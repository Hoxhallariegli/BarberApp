<?php

namespace App\Livewire\Admin\BerberApp\Barbers;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Domain\BerberApp\Barber\Actions\UpdateBarberAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class Edit extends Component
{
        use WithPagination, WithFileUploads;
 public Barber $item;
    public $name = '';
    public $specialization = '';
    public $phone = '';
    public $commission_rate = '';
    public $photo = '';

    public function mount(Barber $barber) { $this->item = $barber; $this->fill($barber->toArray());  }
    public function render() {
        abort_if_cannot('edit_barbers');
        return view('livewire.admin.berber-app.barbers.edit', [
        ])->layout('components.layouts.app')->title(__('barbers.Edit Barber'));
    }
    public function update(UpdateBarberAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = 'uploads/' . $this->photo->store('barbers', 'uploads'); }
 $dto = BarberDTO::fromArray([
            'name' => $this->name,
            'specialization' => $this->specialization,
            'phone' => $this->phone,
            'commission_rate' => $this->commission_rate,
            'photo' => $this->photo,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('barbers.updated')); return to_route('admin.barbers.index'); }
    protected function rules(): array { return Barber::rules($this->item->id); }
}
