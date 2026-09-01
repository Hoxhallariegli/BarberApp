<?php

namespace App\Domain\BerberApp\Barber\Actions;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class UpdateBarberAction
{
    public function execute(Barber $model, BarberDTO $dto): Barber
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'Barbers');
        $model->save();
        return $model->fresh();
    }
}