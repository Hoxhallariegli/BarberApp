<?php

namespace App\Domain\BerberApp\Customer\Actions;

use App\Models\BerberApp\Customer;
use App\Models\AuditTrail;

class DeleteCustomerAction
{
    public function execute(Customer $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Customers');
        return $model->delete(); 
    }
}