<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\BankAuthController;
use App\Http\Controllers\Api\AdminContentController;
use App\Http\Controllers\Api\OtpController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/banks', [\App\Http\Controllers\Api\BankController::class, 'index']);

// Gunakan format class seperti ini agar lebih aman
Route::post('/login', [AuthController::class, 'login']);
Route::post('/bank/login', [BankAuthController::class, 'login']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/content/ads', [AdminContentController::class, 'publicAds']);
Route::get('/content/articles', [AdminContentController::class, 'publicArticles']);

// Rute yang butuh autentikasi (token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
    Route::post('/profile/change-password', [\App\Http\Controllers\Api\ProfileController::class, 'changePassword']);
    Route::delete('/account', [\App\Http\Controllers\Api\ProfileController::class, 'deleteAccount']);

    // Omzet / Chart
    Route::get('/omzet', [\App\Http\Controllers\Api\OmzetController::class, 'index']);
    Route::post('/omzet', [\App\Http\Controllers\Api\OmzetController::class, 'store']);

    // OTP Pengajuan Pinjaman (via WhatsApp)
    Route::post('/otp/send-loan',   [OtpController::class, 'sendLoanOtp']);
    Route::post('/otp/verify-loan', [OtpController::class, 'verifyLoanOtp']);

    // Business Profile & Health Score
    Route::get('/business-profile',  [\App\Http\Controllers\Api\BusinessProfileController::class, 'show']);
    Route::post('/business-profile', [\App\Http\Controllers\Api\BusinessProfileController::class, 'update']);

    // Pengajuan modal (UMKM) — masuk portal bank
    Route::get('/submissions', [\App\Http\Controllers\Api\SubmissionController::class, 'index']);
    Route::post('/submissions', [\App\Http\Controllers\Api\SubmissionController::class, 'store']);
    Route::delete('/submissions/{id}', [\App\Http\Controllers\Api\SubmissionController::class, 'cancel']);

    Route::get('/bank/submissions', [\App\Http\Controllers\Api\BankSubmissionController::class, 'index']);
    Route::get('/bank/submissions/{id}', [\App\Http\Controllers\Api\BankSubmissionController::class, 'show']);
    Route::patch('/bank/submissions/{id}/status', [\App\Http\Controllers\Api\BankSubmissionController::class, 'updateStatus']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/ads', [AdminContentController::class, 'ads']);
    Route::post('/ads', [AdminContentController::class, 'storeAd']);
    Route::put('/ads/{ad}', [AdminContentController::class, 'updateAd']);
    Route::delete('/ads/{ad}', [AdminContentController::class, 'destroyAd']);

    Route::get('/articles', [AdminContentController::class, 'articles']);
    Route::post('/articles', [AdminContentController::class, 'storeArticle']);
    Route::put('/articles/{article}', [AdminContentController::class, 'updateArticle']);
    Route::delete('/articles/{article}', [AdminContentController::class, 'destroyArticle']);

    Route::get('/banks', [AdminContentController::class, 'banks']);
    Route::post('/banks', [AdminContentController::class, 'storeBank']);
    Route::put('/banks/{bank}', [AdminContentController::class, 'updateBank']);
    Route::delete('/banks/{bank}', [AdminContentController::class, 'destroyBank']);
    Route::get('/bank-categories', [AdminContentController::class, 'bankCategories']);
    Route::post('/bank-categories', [AdminContentController::class, 'storeBankCategory']);
    Route::put('/bank-categories/{category}', [AdminContentController::class, 'updateBankCategory']);
    Route::delete('/bank-categories/{category}', [AdminContentController::class, 'destroyBankCategory']);

    Route::get('/users/documents', [AdminContentController::class, 'usersWithDocuments']);
});