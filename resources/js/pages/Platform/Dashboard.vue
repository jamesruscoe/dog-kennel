<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    metrics: {
        total_companies: number;
        total_users: number;
        total_bookings: number;
        active_bookings: number;
    };
    companies: Array<{
        id: number;
        name: string;
        slug: string;
        stripe_onboarding_complete: boolean;
        users_count: number;
        bookings_count: number;
        created_at: string;
    }>;
}>();
</script>

<template>
    <Head title="Platform Dashboard — Dog Desk" />

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
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors"
            >
                Sign out
            </Link>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Platform Overview</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">All companies and usage across Dog Desk</p>
            </div>

            <!-- Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Companies</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ metrics.total_companies }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Users</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ metrics.total_users }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Total Bookings</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ metrics.total_bookings }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Active Bookings</p>
                    <p class="mt-1 text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ metrics.active_bookings }}</p>
                </div>
            </div>

            <!-- Companies table -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Companies</h2>
                </div>
                <div v-if="companies.length === 0" class="px-6 py-12 text-center text-sm text-zinc-400">
                    No companies yet.
                </div>
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-800">
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-400 uppercase tracking-wide">Stripe</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Users</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Bookings</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-400 uppercase tracking-wide">Portal</th>
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
                                    :class="[
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                                        company.stripe_onboarding_complete
                                            ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                                            : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    ]"
                                >
                                    {{ company.stripe_onboarding_complete ? 'Connected' : 'Not configured' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ company.users_count }}</td>
                            <td class="px-6 py-4 text-right text-zinc-600 dark:text-zinc-400">{{ company.bookings_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <a
                                    :href="`/${company.slug}/staff/dashboard`"
                                    class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-medium transition-colors"
                                >
                                    Open portal →
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
