<script setup lang="ts">
defineProps<{
    status: number;
    message: string;
}>();
</script>

<template>
    <Head title="Access Denied" />
    <div class="min-h-screen flex items-center justify-center bg-zinc-50 dark:bg-zinc-950">
        <div class="text-center max-w-md px-6">
            <p class="text-7xl font-bold text-red-500 mb-4">403</p>
            <h1 class="text-2xl font-semibold text-zinc-800 dark:text-zinc-100 mb-3">
                Access Denied
            </h1>
            <p class="text-zinc-500 dark:text-zinc-400 mb-8">
                {{ message }}
            </p>
            <Link
                :href="route('dashboard')"
                class="inline-block bg-zinc-800 dark:bg-zinc-100 text-white dark:text-zinc-900 px-6 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity"
            >
                Go to Dashboard
            </Link>
        </div>
    </div>
</template>
