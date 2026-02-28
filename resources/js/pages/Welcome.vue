<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dog Desk — Dog Boarding Management Platform" />

    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex flex-col">

        <!-- Nav -->
        <header class="w-full px-6 py-4 flex items-center justify-between max-w-6xl mx-auto">
            <div class="flex items-center gap-2">
                <svg class="h-7 w-7 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4.5 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm15 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM7 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM12 22c-4 0-7.5-2.8-7.5-7 0-2.5 1.2-4.5 3-5.8C8.8 8.2 10.3 8 12 8s3.2.2 4.5 1.2c1.8 1.3 3 3.3 3 5.8 0 4.2-3.5 7-7.5 7Z"/>
                </svg>
                <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 tracking-tight">Dog Desk</span>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('signup')"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
                >
                    Get started
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex-1 flex flex-col items-center justify-center text-center px-6 py-20">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 px-4 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 mb-8">
                Multi-kennel SaaS Platform
            </div>

            <h1 class="text-5xl sm:text-6xl font-bold text-zinc-900 dark:text-zinc-50 leading-tight max-w-3xl">
                Dog boarding management<br>
                <span class="text-indigo-600">built for kennel businesses</span>
            </h1>

            <p class="mt-6 text-lg text-zinc-500 dark:text-zinc-400 max-w-xl">
                Run your entire kennel business from one platform. Manage bookings, track care logs, collect payments, and keep owners informed — all in your own branded portal.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center gap-4">
                <Link
                    :href="route('signup')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition-colors"
                >
                    Start your free kennel
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
            </div>
        </main>

        <!-- Features -->
        <section class="w-full max-w-5xl mx-auto px-6 pb-24">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking Management</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Online bookings, approval workflow, capacity tracking, and automated owner notifications.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Daily Care Logs</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Log feeding, walks, medication and more. Owners see everything in real-time from their portal.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Stripe Payments</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Integrated Stripe Connect payments. Collect deposits and full payments directly in your portal.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-800 py-6 text-center text-xs text-zinc-400">
            &copy; {{ new Date().getFullYear() }} Dog Desk. All rights reserved.
        </footer>
    </div>
</template>
