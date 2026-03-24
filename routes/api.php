<?php

use App\Http\Controllers\PacksController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('v1.')->group(function () {
    Route::get('/packs', [PacksController::class, 'index'])->name('packs.index');
    Route::get('/packs/{pack}', [PacksController::class, 'show'])->name('packs.show');
});
