<?php

namespace App\Domain\BerberApp\DeviceToken\Actions;

use App\Models\BerberApp\DeviceToken;
use App\Models\AuditTrail;

class DeleteDeviceTokenAction
{
    public function execute(DeviceToken $model): bool 
    {
        AuditTrail::log($model, 'delete', 'DeviceTokens');
        return $model->delete(); 
    }
}