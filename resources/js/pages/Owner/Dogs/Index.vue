<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import type { Dog } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

defineProps<{
    dogs: Dog[];
}>();
</script>

<template>
    <Head title="My Dogs" />

    <PageHeader title="My Dogs" :subtitle="`${dogs.length} dog${dogs.length === 1 ? '' : 's'} on your profile`">
        <template #actions>
            <Link
                :href="route('owner.dogs.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Dog
            </Link>
        </template>
    </PageHeader>

    <div v-if="dogs.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Link
            v-for="dog in dogs"
            :key="dog.id"
            :href="route('owner.dogs.show', dog.id)"
            class="group rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors"
        >
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600">{{ dog.name }}</h3>
                    <p class="text-sm text-zinc-500">{{ dog.breed }}</p>
                </div>
                <span class="text-2xl">{{ dog.sex === 'male' ? '♂' : '♀' }}</span>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <span v-if="dog.age_years" class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                    {{ dog.age_years }}yr
                </span>
                <span v-if="dog.neutered" class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/20 px-2.5 py-0.5 text-xs text-blue-600 dark:text-blue-400">
                    Neutered
                </span>
                <span
                    :class="dog.vaccination_confirmed
                        ? 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400'
                        : 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400'"
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs"
                >
                    {{ dog.vaccination_confirmed ? 'Vaccinated ✓' : 'Vaccination needed' }}
                </span>
            </div>
            <p class="mt-3 text-xs text-zinc-400">
                {{ dog.bookings_count ?? 0 }} booking{{ (dog.bookings_count ?? 0) === 1 ? '' : 's' }}
            </p>
        </Link>
    </div>

    <EmptyState
        v-else
        title="No dogs yet"
        description="Add your dogs to start making boarding bookings."
        icon="🐾"
    >
        <template #action>
            <Link
                :href="route('owner.dogs.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                Add Your First Dog
            </Link>
        </template>
    </EmptyState>
</template>
