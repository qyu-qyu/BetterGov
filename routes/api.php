<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ResponseDocumentController;
use App\Http\Controllers\OfficeTimeSlotController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\OfficeDashboardController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('services', ServiceController::class);
    Route::apiResource('service-categories', ServiceCategoryController::class);
Route::post('/services/{serviceId}/required-documents', [ServiceController::class, 'attachRequiredDocument']);
    Route::post('/response-documents', [ResponseDocumentController::class, 'store']);
Route::get('/services/{serviceId}/required-documents', [ServiceController::class, 'getRequiredDocuments']);
Route::delete('/services/{serviceId}/required-documents/{documentTypeId}', [ServiceController::class, 'removeRequiredDocument']);

    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::get('/requests/{id}/response-documents', [ResponseDocumentController::class, 'getByRequest']);


Route::get('/feedback', [FeedbackController::class, 'index']);
Route::get('/feedback/{id}', [FeedbackController::class, 'show']);
Route::post('/feedback/{id}/response', [FeedbackController::class, 'respond']);

    Route::get('/notifications', function () {
        return \App\Models\Notification::where('user_id', '=', Auth::id(), 'and')->get();
    });

    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin-only', [AuthController::class, 'adminOnly']);

        Route::put('/requests/{id}/status', [RequestController::class, 'updateStatus']);

        Route::post('/appointment-slots', [OfficeTimeSlotController::class, 'store']);
        Route::get('/appointment-slots', [OfficeTimeSlotController::class, 'index']);
        Route::put('/appointment-slots/{id}', [OfficeTimeSlotController::class, 'update']);
        Route::patch('/appointment-slots/{id}/toggle-active', [OfficeTimeSlotController::class, 'toggleActive']);

Route::get('/office/dashboard/request-status-summary', [OfficeDashboardController::class, 'requestStatusSummary']);
Route::get('/office/dashboard/appointment-summary', [OfficeDashboardController::class, 'appointmentSummary']);
Route::get('/office/dashboard/document-summary', [OfficeDashboardController::class, 'documentSummary']);
Route::get('/office/dashboard', [OfficeDashboardController::class, 'dashboard']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('offices', OfficeController::class);
    });
});  
