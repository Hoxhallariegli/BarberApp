<?php

namespace App\Domain\BerberApp\Reminder\Actions;

use App\Models\BerberApp\Reminder;
use App\Domain\BerberApp\Reminder\DTOs\ReminderDTO;
use App\Models\AuditTrail;

class UpdateReminderAction
{
    public function execute(Reminder $model, ReminderDTO $dto): Reminder
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'Reminders');
        $model->save();
        return $model->fresh();
    }
}