<?php

use App\Http\Controllers\Platform\PlatformController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [PlatformController::class, 'index'])->name('dashboard');
