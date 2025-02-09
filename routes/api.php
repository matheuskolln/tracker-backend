<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTimeController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware(['auth:sanctum', 'verified'])->prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']); // Get all tasks
    Route::post('/', [TaskController::class, 'store']); // Create a new task
    Route::get('{task}', [TaskController::class, 'show']); // Show a specific task
    Route::put('{task}', [TaskController::class, 'update']); // Update task
    Route::delete('{task}', [TaskController::class, 'destroy']); // Delete task
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return response()->json(['message' => 'Email verified successfully.']);
    })->name('verification.verify');

    Route::post('/email/resend', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent.']);
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
