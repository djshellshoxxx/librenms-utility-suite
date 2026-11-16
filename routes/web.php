<?php
use Djshellshoxxx\LibreNMSUtilitySuite\Http\Controllers\DashboardController;
use Djshellshoxxx\LibreNMSUtilitySuite\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('plugin/librenms-utility-suite')->name('librenms-utility-suite.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
