<?php

namespace App\Domain\BerberApp\Barber\Actions;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class CreateBarberAction
{
    public function execute(BarberDTO $dto): Barber 
    {
        $item = Barber::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Barbers');
        return $item;
    }
}