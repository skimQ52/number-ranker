<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/leaderboard', function () {
    return Inertia::render('Leaderboard');
})->name('leaderboard');

Route::get('/votes/{number}', function ($number) {
    return Inertia::render('Votes/Show', [
        'numberId' => $number
    ]);
});

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
