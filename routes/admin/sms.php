<?php

use App\Livewire\Admin\Sms\SmsDevices;
use App\Livewire\Admin\Sms\SmsLogs;
use App\Livewire\Admin\Sms\SmsTemplates;
use App\Livewire\Admin\Sms\SmsSettings;
use App\Livewire\Admin\Calls\CallLogs;
use Illuminate\Support\Facades\Route;

Route::prefix('sms-gateway')->group(function () {
    Route::get('/', SmsSettings::class)->name('admin.sms.index');
    Route::get('logs', SmsLogs::class)->name('admin.sms.logs');
    Route::get('devices', SmsDevices::class)->name('admin.sms.devices');
    Route::get('templates', SmsTemplates::class)->name('admin.sms.templates');
});
