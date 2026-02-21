<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    routeName: string;
    filters: Record<string, string | boolean | number | undefined | null>;
    placeholder?: string;
    searchKey?: string;
}>();

const searchKey = props.searchKey ?? 'search';
const localSearch = ref((props.filters[searchKey] as string) ?? '');

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(localSearch, (value) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        router.get(
            route(props.routeName),
            { ...props.filters, [searchKey]: value || undefined },
            { preserveState: true, replace: true },
        );
    }, 350);
});

function clearSearch() {
    localSearch.value = '';
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative min-w-64">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                v-model="localSearch"
                type="search"
                :placeholder="placeholder ?? 'Search…'"
                class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 py-2 pl-10 pr-8 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
            />
            <button
                v-if="localSearch"
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600"
                @click="clearSearch"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <slot />
    </div>
</template>
