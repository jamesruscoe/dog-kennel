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
use App\Models\Booking;
use App\Models\CareLog;
use App\Models\Dog;
use App\Models\Owner;
use App\Policies\BookingPolicy;
use App\Policies\CareLogPolicy;
use App\Policies\DogPolicy;
use App\Policies\OwnerPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        // ── Policies ──────────────────────────────────────────────────────────
        Gate::policy(Dog::class,     DogPolicy::class);
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(CareLog::class, CareLogPolicy::class);
        Gate::policy(Owner::class,   OwnerPolicy::class);

        // ── Domain event → listener mappings ─────────────────────────────────
        // All listeners implement ShouldQueue — dispatched asynchronously.
        Event::listen(BookingCreated::class,   SendBookingCreatedNotification::class);
        Event::listen(BookingApproved::class,  SendBookingApprovedNotification::class);
        Event::listen(BookingCancelled::class, SendBookingCancelledNotification::class);

        Event::listen(CareLogAdded::class, SendCareLogAddedNotification::class);
    }
}
