<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BerberApp\Barbers\Barbers;
use App\Livewire\Admin\BerberApp\Barbers\Create;
use App\Livewire\Admin\BerberApp\Barbers\Edit;

Route::prefix('barbers')->group(function () {
    Route::get('/', Barbers::class)->name('admin.barbers.index');
    Route::get('create', Create::class)->name('admin.barbers.create');
    Route::get('/{barber}/edit', Edit::class)->name('admin.barbers.edit');
    Route::get('/{barber}/schedule', \App\Livewire\Admin\BerberApp\Barbers\Schedule\BarberScheduleConfig::class)->name('admin.barbers.schedule');
});
