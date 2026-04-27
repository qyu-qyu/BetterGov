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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

   Route::get('/me', [AuthController::class, 'me']);
   Route::post('/logout', [AuthController::class, 'logout']);

   Route::apiResource('services', ServiceController::class);

   Route::get('/requests', [RequestController::class, 'index']);
   Route::post('/requests', [RequestController::class, 'store']);
   Route::get('/requests/{id}', [RequestController::class, 'show']);

   

Route::get('/notifications', function () {
    return \App\Models\Notification::where('user_id', Auth::id())->get();
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

       Route::apiResource('users', UserController::class);
       Route::apiResource('offices', OfficeController::class);
   });
});

