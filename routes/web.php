<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\RequestDocumentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PaymentController;

Route::resource('roles', RoleController::class);
Route::resource('users', UserController::class);
Route::resource('municipalities', MunicipalityController::class);
Route::resource('offices', OfficeController::class);
Route::resource('services', ServiceController::class);
Route::resource('requests', ServiceRequestController::class);
Route::resource('document-types', DocumentTypeController::class);
Route::resource('request-documents', RequestDocumentController::class);
Route::resource('appointments', AppointmentController::class);
Route::resource('payments', PaymentController::class);

Route::get('/', function () {
    return view('welcome');
});
