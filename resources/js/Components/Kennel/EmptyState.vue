<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
    icon?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
        <!-- Default dog/kennel icon if none provided -->
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
            <svg
                v-if="!icon"
                class="h-8 w-8 text-zinc-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <span v-else class="text-2xl">{{ icon }}</span>
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ title }}</h3>
        <p v-if="description" class="mt-1 text-sm text-zinc-500">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>
