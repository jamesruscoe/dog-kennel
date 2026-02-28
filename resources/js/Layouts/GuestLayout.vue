<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { SharedProps } from '@/types/kennel';

const page = usePage<SharedProps>();
const company = computed(() => page.props.company);
</script>

<template>
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex flex-col items-center justify-center px-4 py-12">
        <!-- Brand -->
        <Link href="/" class="flex flex-col items-center gap-1 mb-8">
            <div class="flex items-center gap-2">
                <svg class="h-8 w-8 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4.5 9a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm15 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM7 6a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm10 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM12 22c-4 0-7.5-2.8-7.5-7 0-2.5 1.2-4.5 3-5.8C8.8 8.2 10.3 8 12 8s3.2.2 4.5 1.2c1.8 1.3 3 3.3 3 5.8 0 4.2-3.5 7-7.5 7Z"/>
                </svg>
                <span class="text-xl font-semibold text-zinc-900 dark:text-zinc-100 tracking-tight">Dog Desk</span>
            </div>
            <span v-if="company" class="text-sm text-zinc-500 dark:text-zinc-400">{{ company.name }}</span>
        </Link>

        <!-- Card -->
        <div class="w-full max-w-md rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 px-8 py-8 shadow-sm">
            <slot />
        </div>
    </div>
</template>
