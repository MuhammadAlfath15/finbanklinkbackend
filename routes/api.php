<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\OtpController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/banks', [\App\Http\Controllers\Api\BankController::class, 'index']);

// Gunakan format class seperti ini agar lebih aman
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Rute yang butuh autentikasi (token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);

    // OTP Pengajuan Pinjaman (via WhatsApp)
    Route::post('/otp/send-loan',   [OtpController::class, 'sendLoanOtp']);
    Route::post('/otp/verify-loan', [OtpController::class, 'verifyLoanOtp']);
});