<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import { ref } from 'vue';
import type { Booking, BookingStatus, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    bookings: Paginated<Booking>;
    filters: { status?: string };
}>();

const STATUS_TABS: Array<{ label: string; value: BookingStatus | '' }> = [
    { label: 'All',       value: '' },
    { label: 'Pending',   value: 'pending' },
    { label: 'Approved',  value: 'approved' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const activeTab = ref<BookingStatus | ''>(
    (props.filters.status as BookingStatus) ?? '',
);

function setTab(value: BookingStatus | '') {
    activeTab.value = value;
    router.get(
        tenantRoute('owner.bookings.index'),
        { status: value || undefined },
        { preserveState: true, replace: true },
    );
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="My Bookings" />

    <PageHeader title="My Bookings" :subtitle="`${bookings.total} booking${bookings.total === 1 ? '' : 's'}`">
        <template #actions>
            <Link
                :href="tenantRoute('owner.bookings.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Request Booking
            </Link>
        </template>
    </PageHeader>

    <!-- Status tabs -->
    <div class="mb-4 flex gap-1 border-b border-zinc-200 dark:border-zinc-700">
        <button
            v-for="tab in STATUS_TABS"
            :key="tab.value"
            type="button"
            :class="[
                'px-3 py-2 text-sm font-medium transition-colors border-b-2 -mb-px',
                activeTab === tab.value
                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300',
            ]"
            @click="setTab(tab.value)"
        >
            {{ tab.label }}
        </button>
    </div>

    <!-- Booking list -->
    <div class="space-y-3">
        <template v-if="bookings.data.length > 0">
            <Link
                v-for="booking in bookings.data"
                :key="booking.id"
                :href="tenantRoute('owner.bookings.show', booking.id)"
                class="flex items-center justify-between rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-5 py-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors"
            >
                <div>
                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                        {{ booking.dog?.name ?? '—' }}
                    </p>
                    <p class="text-xs text-zinc-500 mt-0.5">
                        {{ formatDate(booking.check_in_date) }} → {{ formatDate(booking.check_out_date) }}
                        · {{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}
                        <template v-if="booking.amount_display"> · {{ booking.amount_display }}</template>
                    </p>
                </div>
                <StatusBadge :status="booking.status" />
            </Link>

            <Pagination v-if="bookings.last_page > 1" :paginator="bookings" />
        </template>

        <EmptyState
            v-else
            title="No bookings yet"
            description="Request a booking for your dog to get started."
        >
            <template #action>
                <Link
                    :href="tenantRoute('owner.bookings.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    Request Booking
                </Link>
            </template>
        </EmptyState>
    </div>
</template>
