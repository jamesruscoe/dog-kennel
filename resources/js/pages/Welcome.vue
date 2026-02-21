<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex flex-col">

        <!-- Nav -->
        <header class="w-full px-6 py-4 flex items-center justify-between max-w-6xl mx-auto">
            <div class="flex items-center gap-2">
                <!-- Paw icon -->
                <svg class="h-7 w-7 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4.5 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm15 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM7 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM12 22c-4 0-7.5-2.8-7.5-7 0-2.5 1.2-4.5 3-5.8C8.8 8.2 10.3 8 12 8s3.2.2 4.5 1.2c1.8 1.3 3 3.3 3 5.8 0 4.2-3.5 7-7.5 7Z"/>
                </svg>
                <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 tracking-tight">PawStay</span>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('login')"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors"
                >
                    Sign in
                </Link>
                <Link
                    :href="route('register')"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
                >
                    Register
                </Link>
            </div>
        </header>

        <!-- Hero -->
        <main class="flex-1 flex flex-col items-center justify-center text-center px-6 py-20">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 px-4 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 mb-8">
                Professional Dog Boarding &amp; Care
            </div>

            <h1 class="text-5xl sm:text-6xl font-bold text-zinc-900 dark:text-zinc-50 leading-tight max-w-3xl">
                A home away from home<br>
                <span class="text-indigo-600">for your dog</span>
            </h1>

            <p class="mt-6 text-lg text-zinc-500 dark:text-zinc-400 max-w-xl">
                Book trusted overnight care for your dog, track their daily activities, and stay updated every step of the stay.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center gap-4">
                <Link
                    :href="route('register')"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition-colors"
                >
                    Create an account
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </Link>
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-6 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 shadow-sm transition-colors"
                >
                    Sign in to your account
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
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Easy Booking</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Request stays in seconds. Check availability and get confirmed quickly.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Care Logs</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Feeding, walks, medication and more — logged daily so you always know how your dog is doing.</p>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
                    <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Real-time Updates</h3>
                    <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">Instant notifications when your booking is approved and daily email summaries during the stay.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-zinc-200 dark:border-zinc-800 py-6 text-center text-xs text-zinc-400">
            &copy; {{ new Date().getFullYear() }} PawStay. All rights reserved.
        </footer>
    </div>
</template>
