<?php

namespace App\Domain\BerberApp\Barber\Actions;

use App\Models\BerberApp\Barber;
use App\Models\AuditTrail;

class DeleteBarberAction
{
    public function execute(Barber $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Barbers');
        return $model->delete(); 
    }
}