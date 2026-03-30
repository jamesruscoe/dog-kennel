<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Payment } from '@/types/kennel';

<<<<<<< Updated upstream
defineProps<{
=======
const tenantRoute = useTenantRoute();
const connectingStripe = ref(false);
const stripeError = ref<string | null>(null);
const subscribing = ref(false);
const subscriptionError = ref<string | null>(null);
const managingBilling = ref(false);

const props = defineProps<{
>>>>>>> Stashed changes
    stats: {
        total_revenue_pence: number;
        total_revenue_display: string;
        succeeded_payments: number;
        pending_payments: number;
        total_bookings: number;
        paid_bookings: number;
    };
    recentPayments: Array<Payment & {
        booking?: {
            id: number;
            check_in_date: string;
            check_out_date: string;
            dog?: { name: string };
        };
    }>;
<<<<<<< Updated upstream
}>();
=======
    stripe: {
        connected: boolean;
        ready: boolean;
        account_id: string | null;
        onboarding_complete: boolean;
    };
    subscription: {
        status: string | null;
        status_label: string | null;
        is_active: boolean;
        ends_at: string | null;
        has_subscription: boolean;
    };
}>();

async function connectStripe() {
    connectingStripe.value = true;
    stripeError.value = null;
    try {
        const { data } = await axios.post(tenantRoute('staff.finance.connect-stripe'));
        window.location.href = data.url;
    } catch (e: any) {
        stripeError.value = e.response?.data?.error ?? e.response?.data?.errors?.stripe?.[0] ?? 'Failed to connect to Stripe. Please try again.';
        connectingStripe.value = false;
    }
}

async function subscribe() {
    subscribing.value = true;
    subscriptionError.value = null;
    try {
        const { data } = await axios.post(tenantRoute('staff.finance.subscribe'));
        window.location.href = data.url;
    } catch (e: any) {
        subscriptionError.value = e.response?.data?.error ?? 'Failed to start subscription. Please try again.';
        subscribing.value = false;
    }
}

async function manageBilling() {
    managingBilling.value = true;
    try {
        const { data } = await axios.post(tenantRoute('staff.finance.manage-billing'));
        window.location.href = data.url;
    } catch {
        managingBilling.value = false;
    }
}

const subscriptionBadgeClass = (status: string | null) => {
    switch (status) {
        case 'active':
        case 'trialing':
            return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400';
        case 'past_due':
            return 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400';
        case 'canceled':
        case 'unpaid':
        case 'incomplete':
            return 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';
        default:
            return 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400';
    }
};

const subscriptionDotClass = (status: string | null) => {
    switch (status) {
        case 'active':
        case 'trialing':
            return 'bg-emerald-500';
        case 'past_due':
            return 'bg-amber-500';
        case 'canceled':
        case 'unpaid':
        case 'incomplete':
            return 'bg-red-500';
        default:
            return 'bg-zinc-400';
    }
};
>>>>>>> Stashed changes
</script>

<template>
    <KennelLayout>
        <Head title="Finance" />

        <div class="mb-6">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Finance</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Revenue and payment summary for your kennel</p>
        </div>

<<<<<<< Updated upstream
=======
        <!-- Platform Subscription -->
        <div class="mb-8 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Platform Subscription</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Your monthly Dog Desk subscription for using the platform.
                    </p>
                </div>
                <div class="ml-4 shrink-0">
                    <span
                        :class="['inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium', subscriptionBadgeClass(subscription.status)]"
                    >
                        <span :class="['h-1.5 w-1.5 rounded-full', subscriptionDotClass(subscription.status)]" />
                        {{ subscription.status_label ?? 'No Subscription' }}
                    </span>
                </div>
            </div>

            <div v-if="subscription.is_active" class="mt-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                    Your subscription is active.
                    <span v-if="subscription.ends_at">
                        Renews {{ new Date(subscription.ends_at).toLocaleDateString() }}.
                    </span>
                </p>
                <button
                    type="button"
                    :disabled="managingBilling"
                    class="mt-3 inline-flex items-center gap-2 rounded-lg bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                    @click="manageBilling"
                >
                    Manage Billing
                </button>
            </div>

            <div v-else-if="subscription.status === 'canceled'" class="mt-4">
                <p class="mb-3 text-sm text-red-600 dark:text-red-400">
                    Your subscription has been cancelled.
                    <span v-if="subscription.ends_at">
                        Access ends {{ new Date(subscription.ends_at).toLocaleDateString() }}.
                    </span>
                </p>
                <button
                    type="button"
                    :disabled="subscribing"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    @click="subscribe"
                >
                    Resubscribe
                </button>
                <p v-if="subscriptionError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ subscriptionError }}</p>
            </div>

            <div v-else class="mt-4">
                <p class="mb-3 text-sm text-zinc-500 dark:text-zinc-400">
                    Subscribe to Dog Desk to start accepting bookings. You'll be redirected to Stripe to set up your payment.
                </p>
                <button
                    type="button"
                    :disabled="subscribing"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    @click="subscribe"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Subscribe
                </button>
                <p v-if="subscriptionError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ subscriptionError }}</p>
            </div>
        </div>

        <!-- Stripe Connection -->
        <div class="mb-8 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Stripe Connect</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Connect your Stripe account to accept payments for bookings.
                    </p>
                </div>
                <div class="ml-4 shrink-0">
                    <span
                        v-if="stripe.ready"
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-400"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        Connected
                    </span>
                    <span
                        v-else-if="stripe.connected"
                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-medium text-amber-700 dark:text-amber-400"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500" />
                        Onboarding Incomplete
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-zinc-500 dark:text-zinc-400"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-zinc-400" />
                        Not Connected
                    </span>
                </div>
            </div>

            <div v-if="stripe.ready" class="mt-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-sm text-emerald-700 dark:text-emerald-400">
                    Your Stripe account is connected and ready to accept payments. Account ID: <code class="text-xs">{{ stripe.account_id }}</code>
                </p>
            </div>

            <div v-else class="mt-4">
                <p v-if="stripe.connected" class="mb-3 text-sm text-amber-600 dark:text-amber-400">
                    Your Stripe account is linked but onboarding is not complete. Click below to continue setup.
                </p>
                <p v-else class="mb-3 text-sm text-zinc-500 dark:text-zinc-400">
                    You need to connect a Stripe account before you can accept payments. This will redirect you to Stripe to complete onboarding.
                </p>
                <button
                    type="button"
                    :disabled="connectingStripe"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    @click="connectStripe"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    {{ stripe.connected ? 'Continue Stripe Setup' : 'Connect Stripe Account' }}
                </button>
                <p v-if="stripeError" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ stripeError }}</p>
            </div>
        </div>

>>>>>>> Stashed changes
        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Total Revenue</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.total_revenue_display }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Paid Bookings</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.paid_bookings }}</p>
                <p class="text-xs text-zinc-400">of {{ stats.total_bookings }} total</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Succeeded Payments</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.succeeded_payments }}</p>
                <p v-if="stats.pending_payments > 0" class="text-xs text-amber-500">{{ stats.pending_payments }} pending</p>
            </div>
        </div>

        <!-- Recent payments -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Payments</h2>
            </div>

            <div v-if="recentPayments.length === 0" class="px-6 py-12 text-center text-sm text-zinc-400">
                No payments recorded yet.
            </div>

            <table v-else class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-800">
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Dog</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Stay</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    <tr v-for="payment in recentPayments" :key="payment.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-6 py-4 font-medium text-zinc-900 dark:text-zinc-100">
                            {{ payment.booking?.dog?.name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                            <span v-if="payment.booking">
                                {{ payment.booking.check_in_date }} → {{ payment.booking.check_out_date }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="px-6 py-4">
                            <span
                                :class="[
                                    'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                    payment.status === 'succeeded'
                                        ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                        : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                ]"
                            >
                                {{ payment.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-zinc-100">{{ payment.amount_display }}</td>
                        <td class="px-6 py-4 text-right text-zinc-500 dark:text-zinc-400 text-xs">
                            {{ payment.paid_at ? new Date(payment.paid_at).toLocaleDateString() : '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </KennelLayout>
</template>
