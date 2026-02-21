<script setup lang="ts">
import { nextTick, onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import axios from 'axios';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import type { Booking } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    booking: Booking;
    stripe_key: string | null;
}>();

// ─────────────────────────────────────────────────────────────────────────────
// Cancel action
// ─────────────────────────────────────────────────────────────────────────────

const cancelModal = ref<InstanceType<typeof ConfirmModal>>();
const cancelForm = useForm({ cancellation_reason: '' });

function submitCancel() {
    cancelForm.patch(route('owner.bookings.cancel', props.booking.id), {
        onSuccess: () => cancelModal.value?.hide(),
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// Formatters
// ─────────────────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

const ACTIVITY_LABELS: Record<string, string> = {
    feeding: 'Feeding', walking: 'Walking', medication: 'Medication',
    grooming: 'Grooming', play: 'Play', toilet: 'Toilet',
    health_check: 'Health Check', other: 'Other',
};

// ─────────────────────────────────────────────────────────────────────────────
// Payment
// ─────────────────────────────────────────────────────────────────────────────

const paymentPanel   = ref(false);
const paymentReady   = ref(false);
const paymentSuccess = ref(false);
const paymentError   = ref<string | null>(null);
const paymentBusy    = ref(false);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
let stripeInstance: any = null;
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let elementsInstance: any = null;

// Detect redirect back from Stripe 3DS / bank auth
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('redirect_status') === 'succeeded') {
        paymentSuccess.value = true;
    }
});

const needsPayment = props.stripe_key
    && props.booking.status === 'approved'
    && props.booking.payment_status !== 'paid';

const alreadyPaid = props.booking.payment?.status === 'succeeded';

async function openPaymentPanel() {
    paymentBusy.value  = true;
    paymentError.value = null;

    try {
        const { data } = await axios.post(
            route('owner.bookings.payment.intent', props.booking.id),
        );

        paymentPanel.value = true;
        await nextTick(); // wait for #stripe-payment-element to render

        stripeInstance   = await loadStripe(props.stripe_key!);
        elementsInstance = stripeInstance.elements({ clientSecret: data.client_secret });

        const el = elementsInstance.create('payment');
        el.mount('#stripe-payment-element');
        paymentReady.value = true;
    } catch (err: unknown) {
        const e = err as { response?: { data?: { error?: string } } };
        paymentError.value = e.response?.data?.error ?? 'Failed to load payment form. Please try again.';
    } finally {
        paymentBusy.value = false;
    }
}

async function confirmPayment() {
    if (! stripeInstance || ! elementsInstance) return;

    paymentBusy.value  = true;
    paymentError.value = null;

    const { error } = await stripeInstance.confirmPayment({
        elements: elementsInstance,
        confirmParams: {
            return_url: route('owner.bookings.show', props.booking.id),
        },
    });

    // Stripe only returns here on error; on success it redirects to return_url
    if (error) {
        paymentError.value = error.message ?? 'Payment failed. Please try again.';
        paymentBusy.value  = false;
    }
}
</script>

<template>
    <Head :title="`Booking #${booking.id}`" />

    <PageHeader
        :title="`Booking #${booking.id}`"
        :breadcrumbs="[{ label: 'My Bookings', href: route('owner.bookings.index') }, { label: `#${booking.id}` }]"
    >
        <template #actions>
            <ConfirmModal
                v-if="booking.status === 'pending' || booking.status === 'approved'"
                ref="cancelModal"
                title="Cancel Booking"
                description="Are you sure you want to cancel this booking?"
                confirm-label="Cancel Booking"
                :danger="true"
                @confirm="submitCancel"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                >
                    Cancel Booking
                </button>
                <template #body>
                    <textarea
                        v-model="cancelForm.cancellation_reason"
                        rows="3"
                        placeholder="Reason (optional)…"
                        class="mt-3 w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </template>
            </ConfirmModal>
        </template>
    </PageHeader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: booking summary + payment -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Status + dates -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking</h2>
                    <StatusBadge :status="booking.status" />
                </div>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Dog</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ booking.dog?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Check-in</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ formatDate(booking.check_in_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Check-out</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ formatDate(booking.check_out_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Duration</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}</dd>
                    </div>
                    <div v-if="booking.amount_display">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Total</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200 font-medium">{{ booking.amount_display }}</dd>
                    </div>
                </dl>
            </div>

            <!-- ── Payment confirmed ──────────────────────────────────────── -->
            <div
                v-if="alreadyPaid && booking.payment"
                class="rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/20 p-6"
            >
                <div class="flex items-center gap-2 mb-2">
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <h2 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Payment Received</h2>
                </div>
                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                    {{ booking.payment.amount_display }} paid
                    <span v-if="booking.payment.paid_at"> on {{ formatDate(booking.payment.paid_at) }}</span>.
                </p>
            </div>

            <!-- ── Payment due ─────────────────────────────────────────────── -->
            <div
                v-else-if="needsPayment"
                class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-700 dark:bg-blue-900/20 p-6"
            >
                <!-- Redirected back from Stripe with success -->
                <div v-if="paymentSuccess" class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Payment processing</p>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">
                            Your payment is being confirmed. This page will update shortly.
                        </p>
                    </div>
                </div>

                <template v-else>
                    <h2 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-1">Payment Due</h2>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100 mb-4">{{ booking.amount_display }}</p>

                    <!-- Error banner -->
                    <p
                        v-if="paymentError"
                        class="mb-3 rounded-md bg-red-100 dark:bg-red-900/30 px-3 py-2 text-sm text-red-700 dark:text-red-300"
                    >
                        {{ paymentError }}
                    </p>

                    <!-- Step 1: Open payment form -->
                    <div v-if="!paymentPanel">
                        <button
                            type="button"
                            :disabled="paymentBusy"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            @click="openPaymentPanel"
                        >
                            <svg v-if="paymentBusy" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            {{ paymentBusy ? 'Loading…' : 'Pay Now' }}
                        </button>
                    </div>

                    <!-- Step 2: Stripe Payment Element + confirm -->
                    <div v-if="paymentPanel">
                        <!-- Stripe mounts its UI here -->
                        <div id="stripe-payment-element" class="mb-4 min-h-[160px]" />

                        <button
                            v-if="paymentReady"
                            type="button"
                            :disabled="paymentBusy"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                            @click="confirmPayment"
                        >
                            <svg v-if="paymentBusy" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            {{ paymentBusy ? 'Processing…' : 'Confirm Payment' }}
                        </button>
                        <p v-else class="text-xs text-center text-blue-500 animate-pulse">
                            Loading payment form…
                        </p>
                    </div>

                    <p class="mt-3 text-center text-xs text-blue-400 dark:text-blue-500">
                        Secured by Stripe
                    </p>
                </template>
            </div>

            <!-- Notes -->
            <div v-if="booking.notes || booking.special_requirements" class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800/50 dark:bg-amber-900/20 p-6">
                <h2 class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-2">Your Notes</h2>
                <p v-if="booking.notes" class="text-sm text-amber-800 dark:text-amber-400 whitespace-pre-wrap mb-2">{{ booking.notes }}</p>
                <div v-if="booking.special_requirements">
                    <p class="text-xs font-medium text-amber-700 dark:text-amber-500 uppercase tracking-wide mb-1">Special Requirements</p>
                    <p class="text-sm text-amber-800 dark:text-amber-400 whitespace-pre-wrap">{{ booking.special_requirements }}</p>
                </div>
            </div>

            <!-- Rejection / cancellation reason -->
            <div
                v-if="booking.rejection_reason || booking.cancellation_reason"
                class="rounded-lg border border-red-200 bg-red-50 dark:border-red-800/50 dark:bg-red-900/20 p-6"
            >
                <h2 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                    {{ booking.rejection_reason ? 'Rejection Reason' : 'Cancellation Reason' }}
                </h2>
                <p class="text-sm text-red-700 dark:text-red-400 whitespace-pre-wrap">
                    {{ booking.rejection_reason ?? booking.cancellation_reason }}
                </p>
            </div>
        </div>

        <!-- Right: care activity feed -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Care Activity</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Updates from the kennel during your dog's stay.</p>
                </div>

                <ul v-if="booking.care_logs && booking.care_logs.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="log in booking.care_logs" :key="log.id" class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ ACTIVITY_LABELS[log.activity_type] ?? log.activity_type }}
                                </p>
                                <p v-if="log.notes" class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ log.notes }}</p>
                            </div>
                            <p class="shrink-0 text-xs text-zinc-500">{{ formatDateTime(log.occurred_at) }}</p>
                        </div>
                    </li>
                </ul>

                <EmptyState
                    v-else
                    title="No activity yet"
                    :description="booking.status === 'approved' ? 'Activity updates will appear here during your dog\'s stay.' : 'Care activity will be visible once your booking is approved and active.'"
                />
            </div>
        </div>
    </div>
</template>
