<?php

namespace App\Domain\BerberApp\Barber\Actions;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class CreateBarberAction
{
    public function execute(BarberDTO $dto): Barber
    {
        $data = $dto->toArray();
        $data['commission_rate'] = (isset($data['commission_rate']) && is_numeric($data['commission_rate']))
            ? $data['commission_rate']
            : null;

        $item = Barber::create($data);
        AuditTrail::log($item, 'create', 'Barbers');
        return $item;
    }
}
