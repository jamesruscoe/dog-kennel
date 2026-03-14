<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import type { Booking, CareLog, Conversation, Dog } from '@/types/kennel';
import { useTenantRoute } from '@/composables/useTenantRoute';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

defineProps<{
    dogs: Dog[];
    activeBookings: Booking[];
    unreadMessages: Conversation[];
    recentUpdates: CareLog[];
}>();

const ACTIVITY_COLORS: Record<string, string> = {
    feeding:      'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    walking:      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    medication:   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    grooming:     'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
    play:         'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    toilet:       'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
    health_check: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    other:        'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
};

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
    });
}

function truncate(text: string, max: number) {
    return text.length > max ? text.slice(0, max) + '...' : text;
}
</script>

<template>
    <Head title="My Dashboard" />

    <PageHeader title="Welcome Back" subtitle="Manage your dogs and bookings" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Dogs summary -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">My Dogs</h2>
                <Link :href="tenantRoute('owner.dogs.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View all
                </Link>
            </div>
            <div v-if="dogs.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="dog in dogs.slice(0, 4)" :key="dog.id" class="px-6 py-3 flex items-center justify-between">
                        <Link :href="tenantRoute('owner.dogs.show', dog.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                            {{ dog.name }}
                        </Link>
                        <span class="text-xs text-zinc-400">{{ dog.breed }}</span>
                    </li>
                </ul>
            </div>
            <EmptyState v-else title="No dogs" description="Add your first dog to get started.">
                <template #action>
                    <Link :href="tenantRoute('owner.dogs.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Add Dog
                    </Link>
                </template>
            </EmptyState>
        </div>

        <!-- Active bookings -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Active Bookings</h2>
                <Link :href="tenantRoute('owner.bookings.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View all
                </Link>
            </div>
            <div v-if="activeBookings.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="booking in activeBookings" :key="booking.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="tenantRoute('owner.bookings.show', booking.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
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
                    <Link :href="tenantRoute('owner.bookings.create')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        Book a Stay
                    </Link>
                </template>
            </EmptyState>
        </div>

        <!-- Unread Messages widget -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Unread Messages</h2>
                <Link :href="tenantRoute('owner.messages.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View All Messages
                </Link>
            </div>
            <div v-if="unreadMessages.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="conv in unreadMessages" :key="conv.id" class="px-6 py-4">
                        <div class="flex items-start justify-between mb-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ conv.staff_user?.name ?? 'Unknown' }}
                            </p>
                            <span class="text-xs text-zinc-400 shrink-0 ml-2">
                                {{ conv.last_message_at ? formatDateTime(conv.last_message_at) : '' }}
                            </span>
                        </div>
                        <p v-if="conv.latest_message" class="text-sm text-zinc-500 mb-2">
                            {{ truncate(conv.latest_message.body, 80) }}
                        </p>
                        <Link
                            :href="tenantRoute('owner.messages.show', conv.id)"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-indigo-700 transition-colors"
                        >
                            View Entire Message
                        </Link>
                    </li>
                </ul>
            </div>
            <EmptyState v-else title="No Recent Messages" description="You're all caught up!">
                <template #action>
                    <Link :href="tenantRoute('owner.messages.index')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        View All Messages
                    </Link>
                </template>
            </EmptyState>
        </div>

        <!-- Recent Updates widget -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Updates</h2>
                <Link :href="tenantRoute('owner.updates.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    View All Updates
                </Link>
            </div>
            <div v-if="recentUpdates.length > 0">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="log in recentUpdates" :key="log.id" class="px-6 py-4">
                        <div class="flex items-start gap-3">
                            <!-- Thumbnail -->
                            <div v-if="log.media && log.media.some(m => m.signed_url)" class="shrink-0">
                                <img
                                    :src="log.media.find(m => m.signed_url)!.signed_url"
                                    class="h-12 w-12 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700"
                                    alt="Care log photo"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium',
                                            ACTIVITY_COLORS[log.activity_type] ?? ACTIVITY_COLORS.other,
                                        ]"
                                    >
                                        {{ log.activity_label }}
                                    </span>
                                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ log.booking?.dog?.name ?? '—' }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-400 mb-1">{{ formatDateTime(log.occurred_at) }}</p>
                                <p v-if="log.notes" class="text-sm text-zinc-500 truncate">{{ log.notes }}</p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <Link
                                :href="tenantRoute('owner.updates.show', log.id)"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-indigo-700 transition-colors"
                            >
                                View Entire Update
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>
            <EmptyState v-else title="No Recent Updates" description="Care activity for your dogs will appear here.">
                <template #action>
                    <Link :href="tenantRoute('owner.updates.index')" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                        View All Updates
                    </Link>
                </template>
            </EmptyState>
        </div>
    </div>
</template>
