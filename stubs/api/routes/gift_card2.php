<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GiftCardManagementController;


Route::prefix('giftcard')
->middleware([ 'jwt.verify'  ])
->group( function () {
    

    Route::post('/gift-card-by-id',               [GiftCardManagementController::class, 'getBonAvailableAmount']);

    Route::post('/gift-card-contadores',          [GiftCardManagementController::class, 'accountants']);// OK
    Route::post('/gift-card-paginacion',          [GiftCardManagementController::class, 'pagination']);
    Route::post('/gift-card',                     [GiftCardManagementController::class, 'store']);
    Route::get('/gift-card/{id}',                 [GiftCardManagementController::class, 'show']);
    Route::put('/gift-card/{id}',                 [GiftCardManagementController::class, 'update']);
    Route::delete('/gift-card/{id}',              [GiftCardManagementController::class, 'destroy']);
    

});
