<?php

namespace App\Domain\BerberApp\Service\Actions;

use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Service\DTOs\ServiceDTO;
use App\Models\AuditTrail;

class UpdateServiceAction
{
    public function execute(Service $model, ServiceDTO $dto): Service
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'Services');
        $model->save();
        return $model->fresh();
    }
}