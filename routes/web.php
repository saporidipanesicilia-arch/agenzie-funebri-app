<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public / Guest Routes
Route::get('/family/login', function () {
    return view('family.login');
})->name('family.login');

Route::post('/family/login', function () {
    return redirect()->route('family.dashboard');
})->name('family.login.auth');

Route::get('/family/dashboard', function () {
    return view('family.dashboard');
})->name('family.dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/funerals/create-wizard', function () {
        return view('funerals.create_wizard');
    })->name('funerals.create-wizard');

    Route::resource('funerals', \App\Http\Controllers\FuneralController::class);

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/memorial-table', function () {
        return view('memorial_table.index');
    })->name('memorial-table');

    Route::get('/cemetery-registry', function () {
        return view('cemetery.registry');
    })->name('cemetery-registry');

    Route::get('/design-system', function () {
        return view('design-system');
    });
});

require __DIR__ . '/auth.php';
