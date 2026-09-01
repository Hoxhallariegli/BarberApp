<?php

namespace App\Domain\BerberApp\Reminder\Actions;

use App\Models\BerberApp\Reminder;
use App\Domain\BerberApp\Reminder\DTOs\ReminderDTO;
use App\Models\AuditTrail;

class CreateReminderAction
{
    public function execute(ReminderDTO $dto): Reminder 
    {
        $item = Reminder::create($dto->toArray());
        AuditTrail::log($item, 'create', 'Reminders');
        return $item;
    }
}