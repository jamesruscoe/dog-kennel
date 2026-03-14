<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: {
        total_processed_pence: number;
        total_processed_display: string;
        total_payments: number;
        total_fees_pence: number;
        total_fees_display: string;
        connected_accounts: number;
    };
    companies: Array<{
        id: number;
        name: string;
        slug: string;
        stripe_account_id: string;
        application_fee_percent: number;
        total_revenue_pence: number;
        total_revenue_display: string;
        fees_earned_pence: number;
        fees_earned_display: string;
        payment_count: number;
    }>;
}>();
</script>

<template>
    <Head title="Platform Finance — Dog Desk" />

    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
        <!-- Top bar -->
        <header class="flex h-16 items-center justify-between border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-6">
            <div class="flex items-center gap-2">
                <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4.5 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm15 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM7 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM12 22c-4 0-7.5-2.8-7.5-7 0-2.5 1.2-4.5 3-5.8C8.8 8.2 10.3 8 12 8s3.2.2 4.5 1.2c1.8 1.3 3 3.3 3 5.8 0 4.2-3.5 7-7.5 7Z"/>
                </svg>
                <span class="font-semibold text-zinc-900 dark:text-zinc-100">Dog Desk</span>
                <span class="ml-2 rounded-full bg-indigo-100 dark:bg-indigo-900/40 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">Platform Admin</span>
            </div>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('platform.dashboard')"
                    class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
                >
                    Dashboard
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
                >
                    Sign out
                </Link>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Platform Finance</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Platform fees and revenue across all connected kennels</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Platform Fees Earned</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.total_fees_display }}</p>
                    <p class="text-xs text-zinc-400">Your 2% cut</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Total Processed</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.total_processed_display }}</p>
                    <p class="text-xs text-zinc-400">Gross volume</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Payments</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.total_payments }}</p>
                    <p class="text-xs text-zinc-400">Succeeded</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Connected Accounts</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.connected_accounts }}</p>
                    <p class="text-xs text-zinc-400">Stripe Connect</p>
                </div>
            </div>

            <!-- Per-company breakdown -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Revenue by Company</h2>
                </div>

                <div v-if="companies.length === 0" class="px-6 py-12 text-center text-sm text-zinc-400">
                    No connected accounts yet.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800">
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Stripe Account</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Fee %</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Payments</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Gross Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Platform Fees</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr v-for="company in companies" :key="company.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ company.name }}</p>
                                <p class="text-xs text-zinc-400">/{{ company.slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs text-zinc-500 dark:text-zinc-400">{{ company.stripe_account_id }}</code>
                            </td>
                            <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ company.application_fee_percent }}%</td>
                            <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ company.payment_count }}</td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-zinc-100">{{ company.total_revenue_display }}</td>
                            <td class="px-6 py-4 text-right font-medium text-emerald-600 dark:text-emerald-400">{{ company.fees_earned_display }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
