<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

/*
|--------------------------------------------------------------------------
| Authentication pages
|--------------------------------------------------------------------------
*/
Route::get('/',      [AuthController::class, 'showPage'])->name('auth');
Route::get('/auth',  [AuthController::class, 'showPage'])->name('auth.page');
Route::get('/login', [AuthController::class, 'showPage'])->name('login');
Route::get('/register', fn() => redirect('/?tab=register'))->name('register');

/*
|--------------------------------------------------------------------------
| Dashboard stubs — replace with real controllers when built
|--------------------------------------------------------------------------
*/
Route::get('/admin/dashboard',   fn() => view('dashboard', ['role' => 'admin']))->middleware('auth:sanctum')->name('admin.dashboard');
Route::get('/office/dashboard',  fn() => view('dashboard', ['role' => 'office']))->middleware('auth:sanctum')->name('office.dashboard');
Route::get('/citizen/dashboard', fn() => view('dashboard', ['role' => 'citizen']))->middleware('auth:sanctum')->name('citizen.dashboard');
Route::get('/dashboard',         fn() => view('dashboard', ['role' => 'user']))->middleware('auth:sanctum')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Existing resource routes (unchanged)
|--------------------------------------------------------------------------
*/
Route::resource('roles',             RoleController::class);
Route::resource('users',             UserController::class);
Route::resource('municipalities',    MunicipalityController::class);
Route::resource('offices',           OfficeController::class);
Route::resource('services',          ServiceController::class);
Route::resource('requests',          ServiceRequestController::class);
Route::resource('document-types',    DocumentTypeController::class);
Route::resource('request-documents', RequestDocumentController::class);
Route::resource('appointments',      AppointmentController::class);
Route::resource('payments',          PaymentController::class);
