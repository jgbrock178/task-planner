<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('tasks', function () {
        return Inertia::render('Tasks');
    })->name('tasks');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
