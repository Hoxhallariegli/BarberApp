<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BerberApp\Reminders\Reminders;
use App\Livewire\Admin\BerberApp\Reminders\Create;
use App\Livewire\Admin\BerberApp\Reminders\Edit;

Route::prefix('reminders')->group(function () {
    Route::get('/', Reminders::class)->name('admin.reminders.index');
    Route::get('create', Create::class)->name('admin.reminders.create');
    Route::get('/{reminder}/edit', Edit::class)->name('admin.reminders.edit');
});
