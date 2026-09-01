<?php

namespace App\Domain\BerberApp\Reminder\Actions;

use App\Models\BerberApp\Reminder;
use App\Models\AuditTrail;

class DeleteReminderAction
{
    public function execute(Reminder $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Reminders');
        return $model->delete(); 
    }
}