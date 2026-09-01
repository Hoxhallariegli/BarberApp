<?php

namespace App\Domain\BerberApp\Payment\Actions;

use App\Models\BerberApp\Payment;
use App\Models\AuditTrail;

class DeletePaymentAction
{
    public function execute(Payment $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Payments');
        return $model->delete(); 
    }
}