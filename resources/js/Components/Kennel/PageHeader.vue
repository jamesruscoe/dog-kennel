<script setup lang="ts">
interface BreadcrumbItem {
    label: string;
    href?: string;
}

defineProps<{
    title: string;
    subtitle?: string;
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <div class="mb-6">
        <!-- Breadcrumbs -->
        <nav v-if="breadcrumbs?.length" class="mb-2 flex items-center gap-1 text-sm text-zinc-500">
            <template v-for="(crumb, i) in breadcrumbs" :key="i">
                <span v-if="i > 0" class="text-zinc-300">/</span>
                <Link v-if="crumb.href" :href="crumb.href" class="hover:text-zinc-700 transition-colors">
                    {{ crumb.label }}
                </Link>
                <span v-else class="text-zinc-700">{{ crumb.label }}</span>
            </template>
        </nav>

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ title }}</h1>
                <p v-if="subtitle" class="mt-1 text-sm text-zinc-500">{{ subtitle }}</p>
            </div>
            <div v-if="$slots.actions" class="flex items-center gap-2">
                <slot name="actions" />
            </div>
        </div>
    </div>
</template>
