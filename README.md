# PawStay — Dog Boarding Kennel Management System

A full-featured kennel management application built with Laravel 12, Vue 3, and Inertia.js. Provides separate portals for kennel staff and dog owners, with booking management, daily care logging, Stripe payments, and real-time notifications.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Vue 3 + Inertia.js v2 + TypeScript |
| Styling | Tailwind CSS v4 |
| Database | MySQL / SQLite |
| Payments | Stripe (Payment Intents + Webhooks) |
| Charts | Chart.js |
| Auth | Laravel Breeze |
| Dev Server | Laravel Herd + Vite |

---

## Features

### Staff Portal (`/staff/*`)
- **Dashboard** — KPI cards (capacity, check-ins, revenue), 14-day occupancy bar chart, today's movements panel, pending approvals table
- **Owners** — Full CRUD, search/filter, owner profile with linked dogs and booking history
- **Dogs** — Full CRUD, health/vaccination records, dietary and behavioural notes
- **Bookings** — List with status filters, approve/reject/complete actions, rejection reasons, booking detail view
- **Care Logs** — Log daily activities (feeding, walking, medication, grooming, play, toilet, health check) against active bookings; filterable log index
- **Calendar** — Monthly occupancy calendar showing available/full days
- **Settings** — Kennel configuration (capacity, nightly rate, operating days, check-in/out times, booking lead days, terms & conditions)
- **User Management** — Create and delete staff accounts

### Owner Portal (`/owner/*`)
- **Dashboard** — Active bookings, upcoming stays, recent care activity
- **Bookings** — Request bookings with date picker, view status, cancel with reason, care activity feed per booking
- **Stripe Payments** — Pay approved bookings via Stripe Payment Element (supports 3DS / bank auth redirects)
- **Dogs** — Register dogs, manage health records, view booking history per dog

### Notifications
- In-app notification centre with unread count badge
- Database notifications for: booking created, booking approved, booking cancelled, care log added
- Staff alerted on new booking requests
- Daily summary emails dispatched to owners of dogs currently in stay (queued job, scheduled at 20:00)

### Shared Infrastructure
- Role-based access (`staff` / `owner`) enforced via middleware
- Domain exceptions (`OperatingDayException`, `BookingConflictException`, `InvalidBookingDateException`) surfaced as Inertia form validation errors — no 500 pages
- Inertia-aware error pages for 403, 404, and 500
- Dark mode support throughout

---

## Setup

### Prerequisites
- [Laravel Herd](https://herd.laravel.com/) (recommended) or PHP 8.4 + Composer
- Node.js 20+
- MySQL or SQLite

### 1. Clone & install

```bash
git clone https://github.com/YOUR_USERNAME/Laravel-bluprint.git pawstay
cd pawstay
composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pawstay
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Stripe (optional — payments will be hidden if not set)

```dotenv
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

To receive webhooks locally, use the Stripe CLI:
```bash
stripe listen --forward-to https://laravel-bluprint.test/api/webhooks/stripe
```

### 4. Migrate & seed

```bash
php artisan migrate
php artisan db:seed
```

### 5. Run

**With Herd** — visit `https://laravel-bluprint.test` (Herd serves it automatically).

**Without Herd:**
```bash
php artisan serve
npm run dev
```

---

## Demo Accounts

Seeded by `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Staff | `staff@kennel.test` | `password` |
| Staff | `jane@kennel.test` | `password` |
| Owner | `emma@owner.test` | `password` |
| Owner | `liam@owner.test` | `password` |
| Owner | `sofia@owner.test` | `password` |

Demo data includes 8 dogs, 12 bookings in various states (boarding, upcoming, pending, completed, cancelled), and realistic care logs for active stays.

---

## Project Structure

```
app/
├── Enums/
│   ├── BookingStatus.php       (pending, approved, rejected, cancelled, completed)
│   ├── DayOfWeek.php
│   └── UserRole.php            (staff, owner)
├── Events/
│   ├── BookingApproved.php
│   ├── BookingCancelled.php
│   ├── BookingCreated.php
│   └── CareLogAdded.php
├── Exceptions/
│   ├── BookingConflictException.php
│   ├── InvalidBookingDateException.php
│   └── OperatingDayException.php
├── Http/Controllers/
│   ├── Auth/
│   ├── Kennel/                 (DogController, OwnerController, BookingController, ...)
│   ├── Owner/                  (OwnerDashboardController, OwnerBookingController, OwnerDogController, PaymentController)
│   ├── Staff/                  (StaffDashboardController, StaffUserController)
│   └── StripeWebhookController.php
├── Jobs/
│   └── SendDailySummaryEmail.php
├── Listeners/
│   ├── SendBookingApprovedNotification.php
│   ├── SendBookingCancelledNotification.php
│   ├── SendBookingCreatedNotification.php
│   └── SendCareLogAddedNotification.php
├── Models/
│   ├── Booking.php
│   ├── CareLog.php
│   ├── Dog.php
│   ├── KennelSettings.php      (singleton — use KennelSettings::sole())
│   ├── Owner.php
│   ├── Payment.php
│   └── User.php
├── Notifications/
│   ├── BookingApprovedNotification.php
│   ├── BookingCancelledNotification.php
│   ├── BookingCreatedNotification.php
│   ├── CareLogAddedNotification.php
│   ├── DailySummaryNotification.php
│   └── NewBookingAlertNotification.php
└── Services/
    ├── BookingService.php
    ├── CapacityService.php
    ├── CareLogService.php
    ├── DogService.php
    ├── NotificationService.php
    ├── OwnerService.php
    ├── PaymentService.php
    └── UserService.php

resources/js/
├── pages/
│   ├── Welcome.vue
│   ├── Auth/                   (Login, Register, ForgotPassword, ...)
│   ├── Error/                  (Forbidden, NotFound, ServerError)
│   ├── Notifications/Index.vue
│   ├── Owner/
│   │   ├── Dashboard.vue
│   │   ├── Bookings/           (Index, Create, Show)
│   │   └── Dogs/               (Index, Create, Edit, Show)
│   ├── Profile/
│   └── Staff/
│       ├── Dashboard.vue
│       ├── Bookings/           (Index, Create, Show)
│       ├── Calendar/Index.vue
│       ├── CareLogs/           (Index, Create)
│       ├── Dogs/               (Index, Create, Edit, Show)
│       ├── Owners/             (Index, Create, Edit, Show)
│       ├── Settings/Index.vue
│       └── Users/              (Index, Create)
└── Components/Kennel/
    ├── ConfirmModal.vue
    ├── DogForm.vue
    ├── EmptyState.vue
    ├── OwnerForm.vue
    ├── PageHeader.vue
    ├── Pagination.vue
    ├── SearchFilter.vue
    └── StatusBadge.vue
```

---

## Database Schema

| Table | Description |
|-------|-------------|
| `users` | Authentication + role (`staff` / `owner`) |
| `owners` | Owner profiles linked to users |
| `dogs` | Dog records linked to owners |
| `kennel_settings` | Single-row kennel configuration |
| `bookings` | Booking records with status, dates, amounts |
| `care_logs` | Per-booking activity entries |
| `payments` | Stripe payment records |
| `notifications` | Laravel database notifications |

---

## Scheduled Tasks

| Schedule | Job | Description |
|----------|-----|-------------|
| Daily at 20:00 | `SendDailySummaryEmail` | Emails owners a summary of that day's care activity for dogs currently in stay |

Run the scheduler:
```bash
php artisan schedule:run   # manually trigger
php artisan schedule:work  # daemon (local dev)
```

---

## Queue

Notifications and emails are queued. Run a worker:

```bash
php artisan queue:work
```

Or use the `sync` driver in `.env` for local development (no worker needed):

```dotenv
QUEUE_CONNECTION=sync
```
