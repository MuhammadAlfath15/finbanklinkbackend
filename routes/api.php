<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <--- TAMBAHKAN INI (Hapus yang lama kalau salah)
use App\Http\Controllers\AuthController; // <--- PASTIKAN INI BENAR (Tanpa sub-folder Api) 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/banks', [\App\Http\Controllers\Api\BankController::class, 'index']);

// Gunakan format class seperti ini agar lebih aman
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);