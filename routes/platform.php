<?php

use App\Http\Controllers\Platform\PlatformController;
use App\Http\Controllers\Platform\PlatformFinanceController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [PlatformController::class, 'index'])->name('dashboard');
Route::get('/finance', [PlatformFinanceController::class, 'index'])->name('finance');
