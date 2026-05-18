<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

// Home route redirects to login
Route::get('/', function () {

    return redirect('/login');

});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

//    Dashboard route redirects to documents index
    Route::get('/dashboard', function () {

        return redirect('/documents');

    })->name('dashboard');

    // Document resource routes
    Route::resource(
        'documents',
        DocumentController::class
    );

//    Restore document version route
    Route::post(

        '/documents/{document}/restore/{version}',

        [DocumentController::class, 'restore']

    )->name('documents.restore');

});

require __DIR__ . '/auth.php';