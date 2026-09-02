<?php

namespace App\Domain\BerberApp\Barber\Actions;

use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Barber\DTOs\BarberDTO;
use App\Models\AuditTrail;

class UpdateBarberAction
{
    public function execute(Barber $model, BarberDTO $dto): Barber
    {
        $data = $dto->toArray();
        // Sigurohemi që nëse komisioni është bosh ose jo numerik, të ruhet si null
        $data['commission_rate'] = (isset($data['commission_rate']) && is_numeric($data['commission_rate']))
            ? $data['commission_rate']
            : null;

        $model->update($data);
        AuditTrail::log($model, 'update', 'Barbers');

        return $model->fresh();
    }
}
