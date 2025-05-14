<?php

use App\Http\Controllers\NumberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/numbers', [NumberController::class, 'vote']);

Route::get('/numbers', [NumberController::class, 'duo']);
