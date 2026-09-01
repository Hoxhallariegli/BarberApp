<?php

namespace App\Domain\BerberApp\Customer\Actions;

use App\Models\BerberApp\Customer;
use App\Domain\BerberApp\Customer\DTOs\CustomerDTO;
use App\Models\AuditTrail;

class UpdateCustomerAction
{
    public function execute(Customer $model, CustomerDTO $dto): Customer
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'Customers');
        $model->save();
        return $model->fresh();
    }
}