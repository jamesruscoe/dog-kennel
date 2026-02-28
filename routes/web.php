<?php

use App\Http\Controllers\CompanySignupController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ResolveCompanyFromPath;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// ROOT — marketing & company signup  (no company scope)
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [MarketingController::class, 'index'])->name('home');
Route::get('/signup', [CompanySignupController::class, 'create'])->name('signup');
Route::post('/signup', [CompanySignupController::class, 'store'])->name('signup.store');

// Shared profile routes (auth required, no company scope)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─────────────────────────────────────────────────────────────────────────────
// SUPER ADMIN PLATFORM  —  /platform/*
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('/platform')
    ->middleware(['auth', EnsureSuperAdmin::class])
    ->name('platform.')
    ->group(base_path('routes/platform.php'));

// Root auth routes (super admin / platform login only)
// Must be registered BEFORE the /{company} wildcard group to prevent
// /login, /forgot-password etc. from being matched as company slugs.
require __DIR__ . '/auth.php';

// ─────────────────────────────────────────────────────────────────────────────
// TENANT ROUTES  —  /{company}/*
// Company resolved via ResolveCompanyFromPath middleware.
// Auth & role enforcement is handled within tenant.php groups.
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('/{company}')
    ->middleware([ResolveCompanyFromPath::class])
    ->group(base_path('routes/tenant.php'));
