<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/players/{id}/profile', [PlayerController::class, 'profile']);
Route::get('/players/{id}/balance', [PlayerController::class, 'checkBalance']);
Route::get('/players/{id}/transactions', [PlayerController::class, 'transactionHistory']);
