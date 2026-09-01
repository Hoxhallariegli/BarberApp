<?php

namespace App\Domain\BerberApp\DeviceToken\Actions;

use App\Models\BerberApp\DeviceToken;
use App\Domain\BerberApp\DeviceToken\DTOs\DeviceTokenDTO;
use App\Models\AuditTrail;

class CreateDeviceTokenAction
{
    public function execute(DeviceTokenDTO $dto): DeviceToken 
    {
        $item = DeviceToken::create($dto->toArray());
        AuditTrail::log($item, 'create', 'DeviceTokens');
        return $item;
    }
}