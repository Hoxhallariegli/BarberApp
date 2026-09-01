<?php

namespace App\Livewire\Admin\BerberApp\Services;

use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Service\DTOs\ServiceDTO;
use App\Domain\BerberApp\Service\Actions\CreateServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
    use WithPagination;
    public $names = [];
    public $price = '';
    public $duration_minutes = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function mount()
    {
        foreach (get_available_languages() as $lang) {
            $this->names[$lang] = '';
        }
    }

    public function render() { return view('livewire.admin.berber-app.services.quick-create', [
        ]); }

    public function store(CreateServiceAction $action)
    {
        $this->validate();
        $dto = ServiceDTO::fromArray([
            'name' => $this->names,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('service-created', id: $item->id);
        $this->js("Livewire.dispatch('service-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('services.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['names', 'price', 'duration_minutes']);
        foreach (get_available_languages() as $lang) {
            $this->names[$lang] = '';
        }
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array {
        $rules = Service::rules();
        unset($rules['name'], $rules['name.*']);
        foreach (get_available_languages() as $lang) {
            $rules["names.$lang"] = ['required', 'string', 'max:255'];
        }
        return $rules;
    }
}
