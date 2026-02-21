<?php

use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Stripe webhook — no auth, no CSRF (Stripe signs its own payloads)
// ─────────────────────────────────────────────────────────────────────────────
Route::post('/webhooks/stripe', StripeWebhookController::class)->name('stripe.webhook');
