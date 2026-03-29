<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\PacksController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->prefix('v1')->as('v1.')->group(function () {
    Route::get('/packs', [PacksController::class, 'index'])->name('packs.index');
    Route::get('/packs/{pack}', [PacksController::class, 'show'])->name('packs.show');

    Route::get('/cards', [CardsController::class, 'index'])->name('cards.index');
    Route::get('/cards/{card}', [CardsController::class, 'show'])->name('cards.show');
});
