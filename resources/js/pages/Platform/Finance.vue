<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    stats: {
        total_processed_pence: number;
        total_processed_display: string;
        total_payments: number;
        active_subscriptions: number;
        total_companies: number;
    };
    companies: Array<{
        id: number;
        name: string;
        slug: string;
        stripe_account_id: string | null;
        subscription_status: string | null;
        subscription_label: string | null;
        subscription_active: boolean;
        total_revenue_pence: number;
        total_revenue_display: string;
        payment_count: number;
    }>;
}>();

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
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Subscriptions and revenue across all kennels</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Active Subscriptions</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.active_subscriptions }}</p>
                    <p class="text-xs text-zinc-400">of {{ stats.total_companies }} companies</p>
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
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Total Companies</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ stats.total_companies }}</p>
                    <p class="text-xs text-zinc-400">Registered</p>
                </div>
            </div>

            <!-- Per-company breakdown -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Companies</h2>
                </div>

                <div v-if="companies.length === 0" class="px-6 py-12 text-center text-sm text-zinc-400">
                    No companies registered yet.
                </div>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800">
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Subscription</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Stripe Connect</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Payments</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Gross Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        <tr v-for="company in companies" :key="company.id" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ company.name }}</p>
                                <p class="text-xs text-zinc-400">/{{ company.slug }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="['inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium', subscriptionBadgeClass(company.subscription_status)]"
                                >
                                    {{ company.subscription_label ?? 'None' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="company.stripe_account_id" class="text-xs text-zinc-500 dark:text-zinc-400">
                                    <code>{{ company.stripe_account_id }}</code>
                                </span>
                                <span v-else class="text-xs text-zinc-400">Not connected</span>
                            </td>
                            <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ company.payment_count }}</td>
                            <td class="px-6 py-4 text-right font-medium text-zinc-900 dark:text-zinc-100">{{ company.total_revenue_display }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
