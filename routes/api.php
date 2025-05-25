<?php

use App\Http\Controllers\NumberController;
use App\Http\Controllers\VoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('throttle:100,10')->group(function () {
    Route::post('/numbers', [NumberController::class, 'vote']);
    Route::get('/numbers', [NumberController::class, 'duo']);
    Route::get('/rankings', [NumberController::class, 'index']);
});

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/votes/{number}', [VoteController::class, 'votes']);
    Route::get('/wins/{number}', [VoteController::class, 'wins']);
    Route::get('/losses/{number}', [VoteController::class, 'losses']);
});
