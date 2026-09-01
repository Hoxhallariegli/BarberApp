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

class Create extends Component
{
    use WithPagination;
    public $names = []; // Changed from $name = ''
    public $price = '';
    public $duration_minutes = '';

    public function mount()
    {
        foreach (get_available_languages() as $lang) {
            $this->names[$lang] = '';
        }
    }

    public function render() {
        abort_if_cannot('add_services');
        return view('livewire.admin.berber-app.services.create', [
        ])->layout('components.layouts.app')->title(__('services.Add Service'));
    }
    public function store(CreateServiceAction $action) {
        $this->validate();
        $dto = ServiceDTO::fromArray([
            'name' => $this->names,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
        ]);
        $action->execute($dto);
        session()->flash('success', __('services.created'));
        return to_route('admin.services.index');
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
