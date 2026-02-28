<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { SharedProps } from '@/types/kennel';

const page = usePage<SharedProps>();
const company = page.props.company!;
</script>

<template>
    <GuestLayout>
        <Head :title="`${company.name} — Sign in`" />

        <div class="text-center mb-6">
            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                Welcome to {{ company.name }}
            </h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Sign in to manage your dog's bookings and care.
            </p>
        </div>

        <Link
            :href="route('tenant.login', { company: company.slug })"
            class="flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
        >
            Sign in to your account
        </Link>

        <div class="mt-4 relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-zinc-200 dark:border-zinc-700"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white dark:bg-zinc-900 px-2 text-zinc-400 dark:text-zinc-500">or</span>
            </div>
        </div>

        <Link
            :href="route('tenant.register', { company: company.slug })"
            class="mt-4 flex w-full items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-700 px-4 py-2.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors"
        >
            Create an account
        </Link>
    </GuestLayout>
</template>
