<?php

use App\Http\Controllers\Kennel\BookingController;
use App\Http\Controllers\Kennel\CalendarController;
use App\Http\Controllers\Kennel\CareLogController;
use App\Http\Controllers\Kennel\DogController;
use App\Http\Controllers\Kennel\KennelSettingsController;
use App\Http\Controllers\Kennel\NotificationController;
use App\Http\Controllers\Kennel\OwnerController;
use App\Http\Controllers\Owner\OwnerBookingController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerDogController;
use App\Http\Controllers\Owner\PaymentController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\StaffUserController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// STAFF PORTAL  —  /staff/*
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role.staff'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');

        // Owners
        Route::resource('owners', OwnerController::class);

        // Dogs
        Route::resource('dogs', DogController::class);

        // Bookings + status transitions
        Route::resource('bookings', BookingController::class)->only(['index', 'show', 'create', 'store']);
        Route::patch('/bookings/{booking}/approve',  [BookingController::class, 'approve'])->name('bookings.approve');
        Route::patch('/bookings/{booking}/reject',   [BookingController::class, 'reject'])->name('bookings.reject');
        Route::patch('/bookings/{booking}/cancel',   [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');

        // Care Logs
        Route::get('/care-logs', [CareLogController::class, 'index'])->name('care-logs.index');
        Route::get('/bookings/{booking}/care-logs/create', [CareLogController::class, 'create'])->name('care-logs.create');
        Route::post('/bookings/{booking}/care-logs', [CareLogController::class, 'store'])->name('care-logs.store');
        Route::delete('/care-logs/{careLog}', [CareLogController::class, 'destroy'])->name('care-logs.destroy');

        // Calendar
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/occupancy', [CalendarController::class, 'occupancy'])->name('calendar.occupancy');

        // Kennel settings
        Route::get('/settings', [KennelSettingsController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [KennelSettingsController::class, 'update'])->name('settings.update');

        // Staff user management
        Route::get('/users', [StaffUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [StaffUserController::class, 'create'])->name('users.create');
        Route::post('/users', [StaffUserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [StaffUserController::class, 'destroy'])->name('users.destroy');
    });

// ─────────────────────────────────────────────────────────────────────────────
// OWNER PORTAL  —  /owner/*
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role.owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', OwnerDashboardController::class)->name('dashboard');

        // Bookings
        Route::get('/bookings', [OwnerBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [OwnerBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [OwnerBookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [OwnerBookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/cancel', [OwnerBookingController::class, 'cancel'])->name('bookings.cancel');

        // Payment — create a Stripe PaymentIntent for an approved booking
        Route::post('/bookings/{booking}/payment/intent', [PaymentController::class, 'createIntent'])->name('bookings.payment.intent');

        // Dogs (owner manages their own dogs)
        Route::get('/dogs', [OwnerDogController::class, 'index'])->name('dogs.index');
        Route::get('/dogs/create', [OwnerDogController::class, 'create'])->name('dogs.create');
        Route::post('/dogs', [OwnerDogController::class, 'store'])->name('dogs.store');
        Route::get('/dogs/{dog}', [OwnerDogController::class, 'show'])->name('dogs.show');
        Route::get('/dogs/{dog}/edit', [OwnerDogController::class, 'edit'])->name('dogs.edit');
        Route::patch('/dogs/{dog}', [OwnerDogController::class, 'update'])->name('dogs.update');
    });

// ─────────────────────────────────────────────────────────────────────────────
// SHARED  —  authenticated, any role
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::patch('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });
