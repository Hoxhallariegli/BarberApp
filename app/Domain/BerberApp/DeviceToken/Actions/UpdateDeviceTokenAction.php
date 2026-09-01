<?php

namespace App\Domain\BerberApp\DeviceToken\Actions;

use App\Models\BerberApp\DeviceToken;
use App\Domain\BerberApp\DeviceToken\DTOs\DeviceTokenDTO;
use App\Models\AuditTrail;

class UpdateDeviceTokenAction
{
    public function execute(DeviceToken $model, DeviceTokenDTO $dto): DeviceToken
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'DeviceTokens');
        $model->save();
        return $model->fresh();
    }
}