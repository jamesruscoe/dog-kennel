<?php

namespace App\Providers;

use App\Events\BookingApproved;
use App\Events\BookingCancelled;
use App\Events\BookingCreated;
use App\Events\CareLogAdded;
use App\Listeners\SendBookingApprovedNotification;
use App\Listeners\SendBookingCancelledNotification;
use App\Listeners\SendBookingCreatedNotification;
use App\Listeners\SendCareLogAddedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Remove the `data` wrapper from JSON resources so Inertia props are
        // flat — i.e. `dog.id` not `dog.data.id`. Paginated responses retain
        // their own `data` key through Laravel's paginator, unaffected by this.
        JsonResource::withoutWrapping();

        // ── Model behaviour ───────────────────────────────────────────────────
        // Strict mode in non-production: catches lazy-loading and unset
        // attribute access during development. Disabled in production for perf.
        Model::shouldBeStrict(! app()->isProduction());

        // Existing scaffold disables mass-assignment protection globally.
        // All models declare $fillable as documentation / IDE support.
        Model::unguard();

        // ── Domain event → listener mappings ─────────────────────────────────
        // All listeners implement ShouldQueue — dispatched asynchronously.
        Event::listen(BookingCreated::class,   SendBookingCreatedNotification::class);
        Event::listen(BookingApproved::class,  SendBookingApprovedNotification::class);
        Event::listen(BookingCancelled::class, SendBookingCancelledNotification::class);

        Event::listen(CareLogAdded::class, SendCareLogAddedNotification::class);
    }
}
