<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BerberApp\Payments\Payments;
use App\Livewire\Admin\BerberApp\Payments\Create;
use App\Livewire\Admin\BerberApp\Payments\Edit;

Route::prefix('payments')->group(function () {
    Route::get('/', Payments::class)->name('admin.payments.index');
    Route::get('create', Create::class)->name('admin.payments.create');
    Route::get('/{payment}/edit', Edit::class)->name('admin.payments.edit');
});
