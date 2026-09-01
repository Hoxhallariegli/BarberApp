<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BerberApp\DeviceTokens\DeviceTokens;
use App\Livewire\Admin\BerberApp\DeviceTokens\Create;
use App\Livewire\Admin\BerberApp\DeviceTokens\Edit;

Route::prefix('device-tokens')->group(function () {
    Route::get('/', DeviceTokens::class)->name('admin.device-tokens.index');
    Route::get('create', Create::class)->name('admin.device-tokens.create');
    Route::get('/{deviceToken}/edit', Edit::class)->name('admin.device-tokens.edit');
});
