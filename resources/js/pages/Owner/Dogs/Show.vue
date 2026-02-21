<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import type { Booking, Dog } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

defineProps<{
    dog: Dog;
    bookings: Booking[];
}>();
</script>

<template>
    <Head :title="dog.name" />

    <PageHeader
        :title="dog.name"
        :subtitle="dog.breed"
        :breadcrumbs="[{ label: 'My Dogs', href: route('owner.dogs.index') }, { label: dog.name }]"
    >
        <template #actions>
            <Link
                :href="route('owner.dogs.edit', dog.id)"
                class="inline-flex items-center gap-2 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
            >
                Edit Details
            </Link>
            <Link
                :href="route('owner.bookings.create', { dog_id: dog.id })"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                Book a Stay
            </Link>
        </template>
    </PageHeader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: profile -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Profile</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Breed</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.breed }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Sex</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            {{ dog.sex === 'male' ? '♂ Male' : '♀ Female' }}
                            {{ dog.neutered ? '(Neutered)' : '' }}
                        </dd>
                    </div>
                    <div v-if="dog.date_of_birth">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Age</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            {{ dog.age_years }} year{{ dog.age_years === 1 ? '' : 's' }}
                        </dd>
                    </div>
                    <div v-if="dog.weight_kg">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Weight</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.weight_kg }} kg</dd>
                    </div>
                    <div v-if="dog.colour">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Colour</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.colour }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Vaccinations</dt>
                        <dd class="mt-0.5">
                            <span
                                :class="dog.vaccination_confirmed ? 'text-green-600' : 'text-amber-600'"
                                class="font-medium text-sm"
                            >
                                {{ dog.vaccination_confirmed ? '✓ Confirmed' : '⚠ Not confirmed — please update' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Care notes (read-only for owner) -->
            <div
                v-if="dog.medical_notes || dog.dietary_notes || dog.behavioural_notes"
                class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6"
            >
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Care Notes</h2>
                <div class="space-y-4 text-sm">
                    <div v-if="dog.medical_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Medical</h3>
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.medical_notes }}</p>
                    </div>
                    <div v-if="dog.dietary_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Dietary</h3>
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.dietary_notes }}</p>
                    </div>
                    <div v-if="dog.behavioural_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Behavioural</h3>
                        <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.behavioural_notes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: booking history -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking History</h2>
                </div>
                <ul v-if="bookings.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="booking in bookings" :key="booking.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="route('owner.bookings.show', booking.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ new Date(booking.check_in_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                –
                                {{ new Date(booking.check_out_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </Link>
                            <p class="text-xs text-zinc-500">{{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}</p>
                        </div>
                        <StatusBadge :status="booking.status" />
                    </li>
                </ul>
                <EmptyState v-else title="No bookings yet" description="Book a stay for your dog to see the history here." />
            </div>
        </div>
    </div>
</template>
