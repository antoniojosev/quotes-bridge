<?php

declare(strict_types=1);

use AntonioVila\QuotesBridge\Http\Controllers\QuotesController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/quotes')->group(function () {
    Route::get('/', [QuotesController::class, 'index']);
    Route::get('/{id}', [QuotesController::class, 'show'])
        ->where('id', '[0-9]+');
});
