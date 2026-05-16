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
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\ResponseDocumentController;
use App\Http\Controllers\RequestDocumentController;
use App\Http\Controllers\OfficeTimeSlotController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SseController;
use App\Http\Controllers\OfficeDashboardController;
use App\Http\Controllers\OfficePortalController;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Payment webhook (must be public, verified by signature)
Route::post('/webhooks/stripe',   [PaymentController::class, 'stripeWebhook']);
Route::get('/roles',     [AuthController::class, 'roles']);

// SSE stream (auth via token in query string or header)
Route::get('/sse', [SseController::class, 'stream']);

Route::get('/offices',                                    [OfficeController::class,          'index']);
Route::get('/offices/{id}',                               [OfficeController::class,          'show']);
Route::get('/municipalities',                             [MunicipalityController::class,    'index']);
Route::get('/services',                                   [ServiceController::class,         'index']);
Route::get('/services/{id}',                              [ServiceController::class,         'show']);
Route::get('/service-types',                              [ServiceTypeController::class,     'index']);
Route::get('/service-categories',                         [ServiceCategoryController::class, 'index']);
Route::get('/document-types',                             [DocumentTypeController::class,    'index']);
Route::get('/service-templates/{id}',                     [ServiceTemplateController::class, 'show']);
Route::get('/services/{serviceId}/required-documents',    [ServiceController::class,         'getRequiredDocuments']);

// ─── Protected (any authenticated user) ───────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Service templates (filtered by office type for office users; all for admin)
    Route::get('/service-templates', [ServiceTemplateController::class, 'index']);

    // Services (write) - role checks are handled in ServiceController
    Route::post('/services',        [ServiceController::class, 'store']);
    Route::put('/services/{id}',    [ServiceController::class, 'update']);
    Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

    // Requests
    Route::get('/requests',                          [RequestController::class,          'index']);
    Route::post('/requests',                         [RequestController::class,          'store']);
    Route::get('/requests/{id}',                     [RequestController::class,          'show']);
    Route::get('/requests/{id}/response-documents',  [ResponseDocumentController::class, 'getByRequest']);
    Route::get('/requests/{id}/documents',           [RequestDocumentController::class,  'getByRequest']);
    Route::get('/requests/{id}/messages',            [MessageController::class,          'byRequest']);
    Route::get('/request-documents/{id}/download',   [RequestDocumentController::class,  'download']);
    Route::get('/response-documents/{id}/download',  [ResponseDocumentController::class, 'download']);
    Route::delete('/requests/{id}',                   [RequestController::class,          'destroy']);

    // Status update (admin + office role; controller enforces per-office scope)
    Route::put('/requests/{id}/status', [RequestController::class, 'updateStatus']);

    // Documents
    Route::get('/request-documents',  [RequestDocumentController::class,  'index']);
    Route::post('/request-documents', [RequestDocumentController::class,  'store']);
    Route::post('/response-documents',[ResponseDocumentController::class, 'store']);

    // Feedback
    Route::post('/feedback',       [FeedbackController::class, 'store']);
    Route::get('/feedback',        [FeedbackController::class, 'index']);
    Route::get('/feedback/{id}',   [FeedbackController::class, 'show']);
    Route::post('/feedback/{id}/response', [FeedbackController::class, 'respond']);

    // Messages
    Route::get('/messages',       [MessageController::class, 'index']);
    Route::post('/messages',      [MessageController::class, 'store']);
    Route::get('/messages/{id}',  [MessageController::class, 'show']);

    // Notifications
    Route::get('/notifications',             [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read',  [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead']);

    // Payments
    Route::get('/payments',                              [PaymentController::class, 'index']);
    Route::post('/payments',                             [PaymentController::class, 'store']);
    Route::get('/payments/{id}',                         [PaymentController::class, 'show']);
    Route::post('/payments/stripe/intent',               [PaymentController::class, 'stripeIntent']);
    Route::post('/payments/stripe/confirm',              [PaymentController::class, 'stripeConfirm']);

    // Appointments (citizen + office view)
    Route::get('/appointments',                [AppointmentController::class, 'index']);
    Route::post('/appointments',               [AppointmentController::class, 'store']);
    Route::patch('/appointments/{id}/cancel',  [AppointmentController::class, 'cancel']);

    // Time slots (read)
    Route::get('/appointment-slots',         [OfficeTimeSlotController::class, 'index']);
    Route::get('/offices/{officeId}/slots',  [OfficeTimeSlotController::class, 'byOffice']);

    // ─── Office portal ────────────────────────────────────────────────────────
    Route::middleware('role:office')->group(function () {
        Route::get('/office-portal/dashboard',     [OfficePortalController::class,    'dashboard']);
        Route::get('/office-portal/profile',       [OfficePortalController::class,    'profile']);
        Route::put('/office-portal/profile',       [OfficePortalController::class,    'updateProfile']);
        Route::get('/office-portal/notifications', [OfficePortalController::class,    'notifications']);
        Route::post('/services/{serviceId}/required-documents',                     [ServiceController::class, 'attachRequiredDocument']);
        Route::delete('/services/{serviceId}/required-documents/{documentTypeId}',  [ServiceController::class, 'removeRequiredDocument']);
    });

    // ─── Office + Admin shared ────────────────────────────────────────────────
    Route::middleware('role:office,admin')->group(function () {
        // Appointment status management (confirm / decline)
        Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);

        // Service categories
        Route::post('/service-categories',        [ServiceCategoryController::class, 'store']);
        Route::put('/service-categories/{id}',    [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);

        // Time slot management
        Route::post('/appointment-slots',                      [OfficeTimeSlotController::class, 'store']);
        Route::put('/appointment-slots/{id}',                  [OfficeTimeSlotController::class, 'update']);
        Route::patch('/appointment-slots/{id}/toggle-active',  [OfficeTimeSlotController::class, 'toggleActive']);
    });

    // ─── Admin only ───────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Dashboard & analytics
        Route::get('/office/dashboard',                         [OfficeDashboardController::class, 'dashboard']);
        Route::get('/office/dashboard/request-status-summary',  [OfficeDashboardController::class, 'requestStatusSummary']);
        Route::get('/office/dashboard/appointment-summary',     [OfficeDashboardController::class, 'appointmentSummary']);
        Route::get('/office/dashboard/document-summary',        [OfficeDashboardController::class, 'documentSummary']);
        Route::get('/office/dashboard/requests-per-office',     [OfficeDashboardController::class, 'requestsPerOffice']);
        Route::get('/office/dashboard/revenue',                 [OfficeDashboardController::class, 'revenueReport']);

        // User management
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

        // Office management
        Route::apiResource('offices', OfficeController::class)->except(['index', 'show']);

        // Municipality management
        Route::post('/municipalities',         [MunicipalityController::class, 'store']);
        Route::get('/municipalities/{id}',     [MunicipalityController::class, 'show']);
        Route::put('/municipalities/{id}',     [MunicipalityController::class, 'update']);
        Route::delete('/municipalities/{id}',  [MunicipalityController::class, 'destroy']);
    });
});