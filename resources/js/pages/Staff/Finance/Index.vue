<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Payment } from '@/types/kennel';

defineProps<{
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
}>();
</script>

<template>
    <KennelLayout>
        <Head title="Finance" />

        <div class="mb-6">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Finance</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Revenue and payment summary for your kennel</p>
        </div>

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
