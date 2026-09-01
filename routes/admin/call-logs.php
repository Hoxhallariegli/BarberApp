<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\CallLogs\CallLogs;
use App\Livewire\Admin\CallLogs\Create;
use App\Livewire\Admin\CallLogs\Edit;
Route::prefix('call-logs')->group(function () {
    Route::get('/', CallLogs::class)->name('admin.sms.call-logs.index');
    Route::get('create', Create::class)->name('admin.call-logs.create');
    Route::get('/{' . 'callLog' . '}/edit', Edit::class)->name('admin.call-logs.edit');
});
