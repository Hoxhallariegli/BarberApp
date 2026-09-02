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

class Create extends Component
{
        use WithPagination, WithFileUploads;
     public $name = '';
    public $phone = '';
    public $email = '';
    public $photo = '';

    public function render() {
        abort_if_cannot('add_customers');
        return view('livewire.admin.berber-app.customers.create', [
        ])->layout('components.layouts.app')->title(__('customers.Add Customer'));
    }
    public function store(CreateCustomerAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = 'uploads/' . $this->photo->store('customers', 'uploads'); }
 $dto = CustomerDTO::fromArray([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
        ]); $action->execute($dto); session()->flash('success', __('customers.created')); return to_route('admin.customers.index'); }
    protected function rules(): array { return Customer::rules(); }
}
