<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('task.store');
    Route::patch('/tasks/{task}/toggle-completed', [TaskController::class, 'toggleCompleted'])->name('task.toggleCompleted');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
