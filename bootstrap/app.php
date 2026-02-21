<?php

use App\Exceptions\BookingConflictException;
use App\Exceptions\InvalidBookingDateException;
use App\Exceptions\OperatingDayException;
use App\Http\Middleware\EnsureUserIsOwner;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register web middleware once — previous scaffold had a duplicate entry.
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->alias([
            'role.staff' => EnsureUserIsStaff::class,
            'role.owner' => EnsureUserIsOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ── Booking domain exceptions → validation errors ─────────────────────
        // Convert booking service exceptions to 422 ValidationException so
        // Inertia surfaces them in the form error bag (form.errors.booking)
        // instead of blowing up with a 500 page.
        $exceptions->map(
            OperatingDayException::class,
            fn (OperatingDayException $e) => ValidationException::withMessages(['booking' => $e->getMessage()])
        );
        $exceptions->map(
            BookingConflictException::class,
            fn (BookingConflictException $e) => ValidationException::withMessages(['booking' => $e->getMessage()])
        );
        $exceptions->map(
            InvalidBookingDateException::class,
            fn (InvalidBookingDateException $e) => ValidationException::withMessages(['booking' => $e->getMessage()])
        );

        // ── Inertia-aware HTTP error rendering ────────────────────────────────
        // For Inertia requests, render error pages as Inertia responses so Vue
        // handles them within the SPA shell. For non-Inertia, Laravel default.
        $exceptions->renderable(function (HttpException $e, Request $request) {
            if (! $request->header('X-Inertia')) {
                return null; // Fall back to Laravel's default HTML error page
            }

            $status = $e->getStatusCode();

            $page = match ($status) {
                403 => 'Error/Forbidden',
                404 => 'Error/NotFound',
                default => 'Error/ServerError',
            };

            return Inertia::render($page, [
                'status'  => $status,
                'message' => $e->getMessage() ?: match ($status) {
                    403 => 'You do not have permission to access this page.',
                    404 => 'The page you were looking for could not be found.',
                    default => 'An unexpected error occurred.',
                },
            ])->toResponse($request)->setStatusCode($status);
        });
    })
    ->withSchedule(function ($schedule) {
        // Daily summary emails — dispatched at 08:00 UTC each morning (JOB 10).
        // $schedule->job(new \App\Jobs\DispatchDailySummaryEmails)->dailyAt('08:00');
    })
    ->create();
