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

class QuickCreate extends Component
{
        use WithPagination, WithFileUploads;
     public $name = '';
    public $specialization = '';
    public $phone = '';
    public $commission_rate = '';
    public $photo = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.berber-app.barbers.quick-create', [
        ]); }

    public function store(CreateBarberAction $action)
    {
        $this->validate();
        if ($this->photo && !is_string($this->photo)) { $this->photo = $this->photo->store('uploads/barbers', 'uploads'); }
        $dto = BarberDTO::fromArray([
            'name' => $this->name,
            'specialization' => $this->specialization,
            'phone' => $this->phone,
            'commission_rate' => $this->commission_rate,
            'photo' => $this->photo,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('barber-created', id: $item->id);
        $this->js("Livewire.dispatch('barber-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('barbers.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['name', 'specialization', 'phone', 'commission_rate', 'photo']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return Barber::rules(); }
}
