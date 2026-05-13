<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ResponseDocumentController;
use App\Http\Controllers\RequestDocumentController;
use App\Http\Controllers\OfficeTimeSlotController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfficeDashboardController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public office/service browsing
Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{id}', [OfficeController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
Route::get('/services/{serviceId}/required-documents', [ServiceController::class, 'getRequiredDocuments']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Services (write operations)
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
    Route::post('/services/{serviceId}/required-documents', [ServiceController::class, 'attachRequiredDocument']);
    Route::delete('/services/{serviceId}/required-documents/{documentTypeId}', [ServiceController::class, 'removeRequiredDocument']);

    Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
    Route::put('/service-categories/{id}', [ServiceCategoryController::class, 'update']);
    Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);

    // Requests
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);
    Route::get('/requests/{id}/response-documents', [ResponseDocumentController::class, 'getByRequest']);

    // Request documents (citizen uploads)
    Route::get('/request-documents', [RequestDocumentController::class, 'index']);
    Route::post('/request-documents', [RequestDocumentController::class, 'store']);
    Route::get('/requests/{id}/documents', [RequestDocumentController::class, 'getByRequest']);

    // Response documents (office uploads)
    Route::post('/response-documents', [ResponseDocumentController::class, 'store']);

    // Feedback (citizen)
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/{id}', [FeedbackController::class, 'show']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Payments
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);

    // Messages / Chat
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
    Route::get('/requests/{id}/messages', [MessageController::class, 'byRequest']);

    // Appointments (citizen)
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);

    // Office time slots (public read)
    Route::get('/appointment-slots', [OfficeTimeSlotController::class, 'index']);
    Route::get('/offices/{officeId}/slots', [OfficeTimeSlotController::class, 'byOffice']);

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin-only', [AuthController::class, 'adminOnly']);

        Route::put('/requests/{id}/status', [RequestController::class, 'updateStatus']);

        Route::post('/appointment-slots', [OfficeTimeSlotController::class, 'store']);
        Route::put('/appointment-slots/{id}', [OfficeTimeSlotController::class, 'update']);
        Route::patch('/appointment-slots/{id}/toggle-active', [OfficeTimeSlotController::class, 'toggleActive']);

        Route::get('/office/dashboard/request-status-summary', [OfficeDashboardController::class, 'requestStatusSummary']);
        Route::get('/office/dashboard/appointment-summary', [OfficeDashboardController::class, 'appointmentSummary']);
        Route::get('/office/dashboard/document-summary', [OfficeDashboardController::class, 'documentSummary']);
        Route::get('/office/dashboard', [OfficeDashboardController::class, 'dashboard']);

        Route::post('/feedback/{id}/response', [FeedbackController::class, 'respond']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('offices', OfficeController::class)->except(['index', 'show']);
    });
});
