<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameItemController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/items', [GameItemController::class, 'index']);
Route::get('/items/trending', [GameItemController::class, 'trendingItems']);
Route::get('/items/{id}', [GameItemController::class, 'show']);
Route::get('/items/{id}/validate-stock', [GameItemController::class, 'validateStock']);
