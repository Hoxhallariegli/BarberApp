<?php

namespace App\Livewire\Admin\BerberApp\Customers;

use App\Models\BerberApp\Customer;
use App\Domain\BerberApp\Customer\DTOs\CustomerDTO;
use App\Domain\BerberApp\Customer\Actions\CreateCustomerAction;
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
    public $phone = '';
    public $email = '';
    public $photo = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.berber-app.customers.quick-create', [
        ]); }

    public function store(CreateCustomerAction $action)
    {
        $this->validate();
        if ($this->photo && !is_string($this->photo)) { $this->photo = $this->photo->store('uploads/customers', 'uploads'); }
        $dto = CustomerDTO::fromArray([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('customer-created', id: $item->id);
        $this->js("Livewire.dispatch('customer-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('customers.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['name', 'phone', 'email', 'photo']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return Customer::rules(); }
}
