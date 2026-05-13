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

// Citizen portal pages
Route::prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/services',               fn() => view('citizen.services.index'))->name('services.index');
    Route::get('/services/{id}',          fn() => view('citizen.services.show'))->name('services.show');
    Route::get('/requests',               fn() => view('citizen.requests.index'))->name('requests.index');
    Route::get('/requests/{id}',          fn() => view('citizen.requests.show'))->name('requests.show');
    Route::get('/offices',                fn() => view('citizen.offices.index'))->name('offices.index');
    Route::get('/appointments',           fn() => view('citizen.appointments.index'))->name('appointments.index');
    Route::get('/feedback',               fn() => view('citizen.feedback.create'))->name('feedback.create');
    Route::get('/history',                fn() => view('citizen.history.index'))->name('history.index');
});
