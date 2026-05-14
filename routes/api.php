<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\MunicipalityController;
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

// ─── Public ───────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/roles',     [AuthController::class, 'roles']);

// Public browsing (no token required)
Route::get('/offices',                                    [OfficeController::class,         'index']);
Route::get('/offices/{id}',                               [OfficeController::class,         'show']);
Route::get('/municipalities',                             [MunicipalityController::class,   'index']);
Route::get('/services',                                   [ServiceController::class,        'index']);
Route::get('/services/{id}',                              [ServiceController::class,        'show']);
Route::get('/service-categories',                         [ServiceCategoryController::class,'index']);
Route::get('/services/{serviceId}/required-documents',    [ServiceController::class,        'getRequiredDocuments']);

// ─── Protected ────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Services (write)
    Route::post('/services',                                                   [ServiceController::class, 'store']);
    Route::put('/services/{id}',                                               [ServiceController::class, 'update']);
    Route::delete('/services/{id}',                                            [ServiceController::class, 'destroy']);
    Route::post('/services/{serviceId}/required-documents',                    [ServiceController::class, 'attachRequiredDocument']);
    Route::delete('/services/{serviceId}/required-documents/{documentTypeId}', [ServiceController::class, 'removeRequiredDocument']);

    // Service categories (write)
    Route::post('/service-categories',        [ServiceCategoryController::class, 'store']);
    Route::put('/service-categories/{id}',    [ServiceCategoryController::class, 'update']);
    Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);

    // Requests
    Route::get('/requests',                         [RequestController::class,         'index']);
    Route::post('/requests',                        [RequestController::class,         'store']);
    Route::get('/requests/{id}',                    [RequestController::class,         'show']);
    Route::get('/requests/{id}/response-documents', [ResponseDocumentController::class,'getByRequest']);
    Route::get('/requests/{id}/documents',          [RequestDocumentController::class, 'getByRequest']);
    Route::get('/requests/{id}/messages',           [MessageController::class,         'byRequest']);

    // Citizen document uploads
    Route::get('/request-documents',  [RequestDocumentController::class, 'index']);
    Route::post('/request-documents', [RequestDocumentController::class, 'store']);

    // Office response documents
    Route::post('/response-documents', [ResponseDocumentController::class, 'store']);

    // Feedback
    Route::post('/feedback',     [FeedbackController::class, 'store']);
    Route::get('/feedback',      [FeedbackController::class, 'index']);
    Route::get('/feedback/{id}', [FeedbackController::class, 'show']);

    // Notifications
    Route::get('/notifications',            [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Payments
    Route::get('/payments',      [PaymentController::class, 'index']);
    Route::post('/payments',     [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);

    // Messages
    Route::get('/messages',      [MessageController::class, 'index']);
    Route::post('/messages',     [MessageController::class, 'store']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);

    // Appointments (citizen)
    Route::get('/appointments',               [AppointmentController::class, 'index']);
    Route::post('/appointments',              [AppointmentController::class, 'store']);
    Route::patch('/appointments/{id}/cancel', [AppointmentController::class, 'cancel']);

    // Office time slots (read)
    Route::get('/appointment-slots',        [OfficeTimeSlotController::class, 'index']);
    Route::get('/offices/{officeId}/slots', [OfficeTimeSlotController::class, 'byOffice']);

    // ─── Admin only ───────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // Request management
        Route::put('/requests/{id}/status',    [RequestController::class, 'updateStatus']);

        // Feedback responses
        Route::post('/feedback/{id}/response', [FeedbackController::class, 'respond']);

        // Appointment slot management
        Route::post('/appointment-slots',                     [OfficeTimeSlotController::class, 'store']);
        Route::put('/appointment-slots/{id}',                 [OfficeTimeSlotController::class, 'update']);
        Route::patch('/appointment-slots/{id}/toggle-active', [OfficeTimeSlotController::class, 'toggleActive']);

        // Dashboard & analytics
        Route::get('/office/dashboard',                        [OfficeDashboardController::class, 'dashboard']);
        Route::get('/office/dashboard/request-status-summary', [OfficeDashboardController::class, 'requestStatusSummary']);
        Route::get('/office/dashboard/appointment-summary',    [OfficeDashboardController::class, 'appointmentSummary']);
        Route::get('/office/dashboard/document-summary',       [OfficeDashboardController::class, 'documentSummary']);
        Route::get('/office/dashboard/requests-per-office',    [OfficeDashboardController::class, 'requestsPerOffice']);
        Route::get('/office/dashboard/revenue',                [OfficeDashboardController::class, 'revenueReport']);

        // User management
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

        // Office management
        Route::apiResource('offices', OfficeController::class)->except(['index', 'show']);

        // Municipality management
        Route::post('/municipalities',        [MunicipalityController::class, 'store']);
        Route::get('/municipalities/{id}',    [MunicipalityController::class, 'show']);
        Route::put('/municipalities/{id}',    [MunicipalityController::class, 'update']);
        Route::delete('/municipalities/{id}', [MunicipalityController::class, 'destroy']);
    });
});
