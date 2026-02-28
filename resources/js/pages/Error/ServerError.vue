<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { SharedProps } from '@/types/kennel';

defineProps<{
    status: number;
    message: string;
}>();

const page = usePage<SharedProps>();

const homeHref = computed(() => {
    const user = page.props.auth?.user;
    const company = page.props.company;
    if (!user) return '/';
    if (company) {
        if (user.role === 'owner') return route('owner.dashboard', { company: company.slug });
        return route('staff.dashboard', { company: company.slug });
    }
    return route('platform.dashboard');
});
</script>

<template>
    <Head title="Server Error" />
    <div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950">
        <div class="text-center max-w-md px-6">
            <p class="text-7xl font-bold text-amber-500 mb-4">{{ status }}</p>
            <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-3">
                Something went wrong
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 mb-8">
                {{ message }}
            </p>
            <Link
                :href="homeHref"
                class="inline-block bg-zinc-800 dark:bg-zinc-100 text-white dark:text-zinc-900 px-6 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity"
            >
                Go to Dashboard
            </Link>
        </div>
    </div>
</template>
