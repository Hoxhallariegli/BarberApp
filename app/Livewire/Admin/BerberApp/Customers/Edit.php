<?php

namespace App\Livewire\Admin\BerberApp\Customers;

use App\Models\BerberApp\Customer;
use App\Domain\BerberApp\Customer\DTOs\CustomerDTO;
use App\Domain\BerberApp\Customer\Actions\UpdateCustomerAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class Edit extends Component
{
        use WithPagination, WithFileUploads;
 public Customer $item;
    public $name = '';
    public $phone = '';
    public $email = '';
    public $photo = '';

    public function mount(Customer $customer) { $this->item = $customer; $this->fill($customer->toArray());  }
    public function render() {
        abort_if_cannot('edit_customers');
        return view('livewire.admin.berber-app.customers.edit', [
        ])->layout('components.layouts.app')->title(__('customers.Edit Customer'));
    }
    public function update(UpdateCustomerAction $action) { $this->validate();         if ($this->photo && !is_string($this->photo)) { $this->photo = $this->photo->store('uploads/customers', 'uploads'); }
 $dto = CustomerDTO::fromArray([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'photo' => $this->photo,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('customers.updated')); return to_route('admin.customers.index'); }
    protected function rules(): array { return Customer::rules($this->item->id); }
}
