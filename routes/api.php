<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTimeController;
use Illuminate\Http\Request;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:sanctum')->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']); // Get all tasks
    Route::post('/', [TaskController::class, 'store']); // Create a new task
    Route::get('{task}', [TaskController::class, 'show']); // Show a specific task
    Route::put('{task}', [TaskController::class, 'update']); // Update task
    Route::delete('{task}', [TaskController::class, 'destroy']); // Delete task
    Route::patch('{task}/status', [TaskController::class, 'changeStatus']); // Change task status
    Route::post('{task}/start-timer', [TaskTimeController::class, 'startTimer']); // Start timer
    Route::post('{task}/stop-timer', [TaskTimeController::class, 'stopTimer']); // Stop timer
    Route::post('{task}/manual-time', [TaskTimeController::class, 'manualTimeEntry']); // Manual time entry
    Route::get('{task}/times', [TaskTimeController::class, 'index']); // Get all task times
});
