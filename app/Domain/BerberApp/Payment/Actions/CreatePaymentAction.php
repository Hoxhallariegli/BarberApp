<?php

namespace App\Domain\BerberApp\Payment\Actions;

use App\Models\BerberApp\Payment;
use App\Domain\BerberApp\Payment\DTOs\PaymentDTO;
use App\Models\AuditTrail;

class CreatePaymentAction
{
    public function execute(PaymentDTO $dto): Payment 
    {
        $item = Payment::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Payments');
        return $item;
    }
}