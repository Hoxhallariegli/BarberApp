<?php

namespace App\Livewire\Admin\BerberApp\Services;

use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Service\DTOs\ServiceDTO;
use App\Domain\BerberApp\Service\Actions\UpdateServiceAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Edit extends Component
{
    use WithPagination;
    public Service $item;
    public $names = [];
    public $price = '';
    public $duration_minutes = '';

    public function mount(Service $service)
    {
        $this->item = $service;
        $this->price = $service->price;
        $this->duration_minutes = $service->duration_minutes;

        // We need the raw array of names
        $this->names = $service->getRawOriginal('name');
        if (is_string($this->names)) {
            $this->names = json_decode($this->names, true) ?: [];
        }

        foreach (get_available_languages() as $lang) {
            if (!isset($this->names[$lang])) {
                $this->names[$lang] = '';
            }
        }
    }
    public function render() {
        abort_if_cannot('edit_services');
        return view('livewire.admin.berber-app.services.edit', [
        ])->layout('components.layouts.app')->title(__('services.Edit Service'));
    }
    public function update(UpdateServiceAction $action) {
        $this->validate();
        $dto = ServiceDTO::fromArray([
            'name' => $this->names,
            'price' => $this->price,
            'duration_minutes' => $this->duration_minutes,
        ]);
        $action->execute($this->item, $dto);
        session()->flash('success', __('services.updated'));
        return to_route('admin.services.index');
    }
    protected function rules(): array {
        $rules = Service::rules($this->item->id);
        unset($rules['name'], $rules['name.*']);
        foreach (get_available_languages() as $lang) {
            $rules["names.$lang"] = ['required', 'string', 'max:255'];
        }
        return $rules;
    }
}
