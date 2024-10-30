<?php

use Modules\MultiCurrency\Http\Controllers\MultiCurrencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {
    Route::prefix('multicurrency')->group(function () {
        Route::get('/install', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'index']);
        Route::post('/install', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'install']);
        Route::get('/install/update', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'uninstall']);

        Route::get('/', [Modules\MultiCurrency\Http\Controllers\MultiCurrencyController::class, 'index']);
	});
});

Route::prefix('multicurrency')->group(function() {
    Route::get('/', 'MultiCurrencyController@index');
});

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {
    Route::resource('/multi_currency_settings', MultiCurrencyController::class);
    Route::post('/exchange-rate/{id}', [MultiCurrencyController::class, 'exchangeRate']);
    Route::get('/api-currency-rate/{id}', [MultiCurrencyController::class, 'apiCurrenyRate']);
});

Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu', 'SetSessionData', 'timezone')->prefix('multicurrency')->group(function () {
    Route::get('/install', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'index']);
    Route::get('/install/update', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\MultiCurrency\Http\Controllers\InstallController::class, 'uninstall']);
});
