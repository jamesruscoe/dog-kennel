<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useTenantRoute } from '@/composables/useTenantRoute';
import type { Payment } from '@/types/kennel';
import axios from 'axios';
import { ref } from 'vue';

const tenantRoute = useTenantRoute();
const connectingStripe = ref(false);
const stripeError = ref<string | null>(null);

const props = defineProps<{
    stats: {
        total_revenue_pence: number;
        total_revenue_display: string;
        succeeded_payments: number;
        pending_payments: number;
        total_bookings: number;
        paid_bookings: number;
        forecast_pence: number;
        forecast_display: string;
        outstanding_pence: number;
        outstanding_display: string;
    };
    recentPayments: Array<Payment & {
        booking?: {
            id: number;
            check_in_date: string;
            check_out_date: string;
            dog?: { name: string };
        };
    }>;
    stripe: {
        connected: boolean;
        ready: boolean;
        account_id: string | null;
        onboarding_complete: boolean;
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
</script>

<template>
    <KennelLayout>
        <Head title="Finance" />

        <div class="mb-6">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Finance</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Revenue and payment summary for your kennel</p>
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

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Revenue Collected</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.total_revenue_display }}</p>
                <p class="text-xs text-zinc-400">{{ stats.succeeded_payments }} payment{{ stats.succeeded_payments === 1 ? '' : 's' }}</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Awaiting Payment</p>
                <p class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ stats.forecast_display }}</p>
                <p class="text-xs text-zinc-400">Approved, unpaid</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Total Outstanding</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.outstanding_display }}</p>
                <p class="text-xs text-zinc-400">All active bookings</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Paid Bookings</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.paid_bookings }}</p>
                <p class="text-xs text-zinc-400">of {{ stats.total_bookings }} total</p>
            </div>
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Pending Payments</p>
                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.pending_payments }}</p>
                <p v-if="stats.pending_payments > 0" class="text-xs text-amber-500">Processing</p>
                <p v-else class="text-xs text-zinc-400">None</p>
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
