<script setup lang="ts">
import type { Paginated } from '@/types/kennel';

defineProps<{
    paginator: Pick<Paginated<unknown>, 'current_page' | 'last_page' | 'from' | 'to' | 'total' | 'links'>;
}>();
</script>

<template>
    <div class="flex items-center justify-between border-t border-zinc-200 dark:border-zinc-700 px-4 py-3 sm:px-6">
        <!-- Mobile summary -->
        <div class="flex flex-1 justify-between sm:hidden">
            <Link
                v-if="paginator.links[0]?.url"
                :href="paginator.links[0].url"
                class="relative inline-flex items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
            >
                Previous
            </Link>
            <span v-else class="relative inline-flex items-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-300 cursor-not-allowed">
                Previous
            </span>
            <Link
                v-if="paginator.links[paginator.links.length - 1]?.url"
                :href="paginator.links[paginator.links.length - 1].url!"
                class="relative ml-3 inline-flex items-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
            >
                Next
            </Link>
            <span v-else class="relative ml-3 inline-flex items-center rounded-md border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-300 cursor-not-allowed">
                Next
            </span>
        </div>

        <!-- Desktop -->
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Showing
                <span class="font-medium">{{ paginator.from ?? 0 }}</span>
                to
                <span class="font-medium">{{ paginator.to ?? 0 }}</span>
                of
                <span class="font-medium">{{ paginator.total }}</span>
                results
            </p>

            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <template v-for="(link, i) in paginator.links" :key="i">
                    <!-- Prev / Next arrows use index 0 and last -->
                    <Link
                        v-if="link.url && (i === 0 || i === paginator.links.length - 1)"
                        :href="link.url"
                        :class="[
                            'relative inline-flex items-center px-2 py-2 text-zinc-400 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 focus:z-20 focus:outline-offset-0',
                            i === 0 ? 'rounded-l-md' : 'rounded-r-md',
                        ]"
                        v-html="link.label"
                    />
                    <span
                        v-else-if="!link.url && (i === 0 || i === paginator.links.length - 1)"
                        :class="[
                            'relative inline-flex items-center px-2 py-2 text-zinc-300 ring-1 ring-inset ring-zinc-300 cursor-not-allowed',
                            i === 0 ? 'rounded-l-md' : 'rounded-r-md',
                        ]"
                        v-html="link.label"
                    />

                    <!-- Page numbers -->
                    <Link
                        v-else-if="link.url && !link.active"
                        :href="link.url"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-zinc-900 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 focus:z-20 focus:outline-offset-0"
                        v-html="link.label"
                    />
                    <span
                        v-else-if="link.active"
                        aria-current="page"
                        class="relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-300 cursor-not-allowed"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </div>
</template>
