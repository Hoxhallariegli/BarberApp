<?php

namespace App\Domain\BerberApp\Customer\Actions;

use App\Models\BerberApp\Customer;
use App\Domain\BerberApp\Customer\DTOs\CustomerDTO;
use App\Models\AuditTrail;

class CreateCustomerAction
{
    public function execute(CustomerDTO $dto): Customer 
    {
        $item = Customer::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Customers');
        return $item;
    }
}