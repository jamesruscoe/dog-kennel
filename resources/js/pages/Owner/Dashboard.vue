<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import type { Booking, Dog } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

defineProps<{
    dogs: Dog[];
    activeBookings: Booking[];
}>();
</script>

<template>
    <Head title="My Dashboard" />

    <PageHeader title="Welcome Back" subtitle="Manage your dogs and bookings" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Dogs summary -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">My Dogs</h2>
                <Link :href="route('owner.dogs.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View all
                </Link>
            </div>
            <div v-if="dogs.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="dog in dogs.slice(0, 4)" :key="dog.id" class="px-6 py-3 flex items-center justify-between">
                        <Link :href="route('owner.dogs.show', dog.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                            {{ dog.name }}
                        </Link>
                        <span class="text-xs text-zinc-400">{{ dog.breed }}</span>
                    </li>
                </ul>
            </div>
            <EmptyState v-else title="No dogs" description="Add your first dog to get started.">
                <template #action>
                    <Link :href="route('owner.dogs.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Add Dog
                    </Link>
                </template>
            </EmptyState>
        </div>

        <!-- Active bookings -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Active Bookings</h2>
                <Link :href="route('owner.bookings.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View all
                </Link>
            </div>
            <div v-if="activeBookings.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="booking in activeBookings" :key="booking.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="route('owner.bookings.show', booking.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ booking.dog?.name }}
                            </Link>
                            <p class="text-xs text-zinc-500">
                                {{ new Date(booking.check_in_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) }}
                                –
                                {{ new Date(booking.check_out_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </p>
                        </div>
                        <StatusBadge :status="booking.status" />
                    </li>
                </ul>
            </div>
            <EmptyState v-else title="No active bookings" description="Book a stay for your dog.">
                <template #action>
                    <Link :href="route('owner.bookings.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Book a Stay
                    </Link>
                </template>
            </EmptyState>
        </div>
    </div>
</template>
