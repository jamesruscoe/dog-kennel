<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import type { AppNotification } from '@/types/kennel';

interface PaginatedNotifications {
    data: AppNotification[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    notifications: PaginatedNotifications;
}>();

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function timeAgo(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diff / 60_000);
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days}d ago`;
    return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// Notification rendering
// ─────────────────────────────────────────────────────────────────────────────

interface NotificationVM {
    id: string;
    icon: string;
    iconBg: string;
    title: string;
    message: string;
    href: string | null;
    read_at: string | null;
    created_at: string;
}

function mapNotification(n: AppNotification): NotificationVM {
    const d = n.data as Record<string, string>;

    switch (n.type) {
        case 'BookingCreatedNotification':
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '📋', iconBg: 'bg-blue-100 dark:bg-blue-900/40',
                title: 'Booking Request Received',
                message: d.message ?? `Booking request for ${d.dog_name ?? 'your dog'}.`,
                href: d.booking_id ? route('owner.bookings.show', d.booking_id) : null,
            };
        case 'BookingApprovedNotification':
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '✅', iconBg: 'bg-green-100 dark:bg-green-900/40',
                title: 'Booking Approved',
                message: d.message ?? `Your booking for ${d.dog_name ?? 'your dog'} has been approved.`,
                href: d.booking_id ? route('owner.bookings.show', d.booking_id) : null,
            };
        case 'BookingCancelledNotification':
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '❌', iconBg: 'bg-red-100 dark:bg-red-900/40',
                title: 'Booking Cancelled',
                message: d.message ?? `A booking for ${d.dog_name ?? 'your dog'} has been cancelled.`,
                href: d.booking_id ? route('owner.bookings.show', d.booking_id) : null,
            };
        case 'CareLogAddedNotification':
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '🐾', iconBg: 'bg-amber-100 dark:bg-amber-900/40',
                title: 'Care Activity Logged',
                message: d.message ?? `${d.activity_label ?? 'Activity'} logged for ${d.dog_name ?? 'your dog'}.`,
                href: d.booking_id ? route('owner.bookings.show', d.booking_id) : null,
            };
        case 'NewBookingAlertNotification':
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '🔔', iconBg: 'bg-purple-100 dark:bg-purple-900/40',
                title: 'New Booking Request',
                message: d.message ?? `New booking request from ${d.owner_name ?? 'an owner'}.`,
                href: d.booking_id ? route('staff.bookings.show', d.booking_id) : null,
            };
        default:
            return {
                id: n.id, read_at: n.read_at, created_at: n.created_at,
                icon: '🔔', iconBg: 'bg-zinc-100 dark:bg-zinc-800',
                title: n.type.replace(/Notification$/, '').replace(/([A-Z])/g, ' $1').trim(),
                message: d.message ?? '',
                href: null,
            };
    }
}

const items = computed<NotificationVM[]>(() =>
    props.notifications.data.map(mapNotification),
);

const unreadCount = computed(() =>
    props.notifications.data.filter((n) => !n.read_at).length,
);

function markAllRead() {
    router.patch(route('notifications.read-all'), {}, {
        preserveScroll: true,
        onSuccess: () => router.reload({ only: ['notifications'] }),
    });
}
</script>

<template>
    <KennelLayout>
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <PageHeader title="Notifications">
                <template #actions>
                    <button
                        v-if="unreadCount > 0"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                        @click="markAllRead"
                    >
                        Mark all as read
                    </button>
                </template>
            </PageHeader>

            <!-- Summary -->
            <p v-if="notifications.total > 0" class="mb-6 text-sm text-zinc-500 dark:text-zinc-400">
                {{ notifications.total }} notification{{ notifications.total === 1 ? '' : 's' }}
                <template v-if="unreadCount > 0">
                    &mdash; <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ unreadCount }} unread</span>
                </template>
            </p>

            <!-- Empty state -->
            <EmptyState
                v-if="notifications.data.length === 0"
                icon="🔔"
                title="No notifications yet"
                description="You'll see booking updates and care activity here."
            />

            <!-- Notification list -->
            <ul v-else class="divide-y divide-zinc-200 dark:divide-zinc-700 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <li
                    v-for="item in items"
                    :key="item.id"
                    :class="[
                        'relative flex gap-4 px-5 py-4 transition-colors',
                        item.read_at
                            ? 'bg-white dark:bg-zinc-900'
                            : 'bg-blue-50 dark:bg-blue-950/20',
                    ]"
                >
                    <!-- Unread indicator dot -->
                    <span
                        v-if="!item.read_at"
                        class="absolute left-1.5 top-1/2 -translate-y-1/2 h-2 w-2 rounded-full bg-blue-500"
                    />

                    <!-- Icon -->
                    <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-xl', item.iconBg]">
                        {{ item.icon }}
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                                {{ item.title }}
                            </p>
                            <time
                                :datetime="item.created_at"
                                :title="formatDate(item.created_at)"
                                class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500"
                            >
                                {{ timeAgo(item.created_at) }}
                            </time>
                        </div>
                        <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ item.message }}
                        </p>
                        <Link
                            v-if="item.href"
                            :href="item.href"
                            class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            View details
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>
                </li>
            </ul>

            <Pagination v-if="notifications.last_page > 1" :paginator="notifications" class="mt-6" />
        </div>
    </KennelLayout>
</template>
