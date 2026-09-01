<?php

namespace App\Domain\BerberApp\Service\Actions;

use App\Models\BerberApp\Service;
use App\Models\AuditTrail;

class DeleteServiceAction
{
    public function execute(Service $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Services');
        return $model->delete(); 
    }
}