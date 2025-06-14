<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\Settings\ApiTokenController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('/settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('/settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/toggle-completed', [TaskController::class, 'toggleCompleted'])->name('tasks.toggleCompleted');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
