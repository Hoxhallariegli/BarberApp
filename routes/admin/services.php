<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\BerberApp\Services\Services;
use App\Livewire\Admin\BerberApp\Services\Create;
use App\Livewire\Admin\BerberApp\Services\Edit;

Route::prefix('services')->group(function () {
    Route::get('/', Services::class)->name('admin.services.index');
    Route::get('create', Create::class)->name('admin.services.create');
    Route::get('/{service}/edit', Edit::class)->name('admin.services.edit');
});
