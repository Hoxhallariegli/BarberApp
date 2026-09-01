<?php

namespace App\Domain\BerberApp\Service\Actions;

use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Service\DTOs\ServiceDTO;
use App\Models\AuditTrail;

class CreateServiceAction
{
    public function execute(ServiceDTO $dto): Service 
    {
        $item = Service::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Services');
        return $item;
    }
}