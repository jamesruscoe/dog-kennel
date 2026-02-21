<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import SearchFilter from '@/Components/Kennel/SearchFilter.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import { ref } from 'vue';
import type { Booking, BookingStatus, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    bookings: Paginated<Booking>;
    filters: { status?: string; search?: string; date_from?: string; date_to?: string };
}>();

const STATUS_TABS: Array<{ label: string; value: BookingStatus | '' }> = [
    { label: 'All',       value: '' },
    { label: 'Pending',   value: 'pending' },
    { label: 'Approved',  value: 'approved' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
    { label: 'Rejected',  value: 'rejected' },
];

const activeTab = ref<BookingStatus | ''>(
    (props.filters.status as BookingStatus) ?? '',
);

function setTab(value: BookingStatus | '') {
    activeTab.value = value;
    router.get(
        route('staff.bookings.index'),
        { ...props.filters, status: value || undefined },
        { preserveState: true, replace: true },
    );
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Bookings" />

    <PageHeader title="Bookings" :subtitle="`${bookings.total} booking${bookings.total === 1 ? '' : 's'}`">
        <template #actions>
            <Link
                :href="route('staff.bookings.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Booking
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

    <!-- Search -->
    <div class="mb-4">
        <SearchFilter
            route-name="staff.bookings.index"
            :filters="filters"
            placeholder="Search dog or owner name…"
        />
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <table v-if="bookings.data.length > 0" class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Dog / Owner</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Check-in</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Check-out</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Nights</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Amount</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Status</th>
                    <th class="relative px-4 py-3"><span class="sr-only">View</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                    v-for="booking in bookings.data"
                    :key="booking.id"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <td class="px-4 py-3">
                        <Link :href="route('staff.bookings.show', booking.id)" class="group">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600">
                                {{ booking.dog?.name ?? '—' }}
                            </p>
                            <p class="text-xs text-zinc-500">
                                {{ booking.dog?.owner?.name ?? '—' }}
                            </p>
                        </Link>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ formatDate(booking.check_in_date) }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ formatDate(booking.check_out_date) }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ booking.nights }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ booking.amount_display ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <StatusBadge :status="booking.status" />
                    </td>
                    <td class="px-4 py-3 text-right">
                        <Link
                            :href="route('staff.bookings.show', booking.id)"
                            class="text-xs text-zinc-500 hover:text-indigo-600 transition-colors"
                        >
                            View
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState
            v-else
            title="No bookings found"
            :description="filters.search ? `No results for '${filters.search}'.` : 'No bookings match the current filters.'"
        >
            <template #action>
                <Link
                    :href="route('staff.bookings.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    New Booking
                </Link>
            </template>
        </EmptyState>

        <Pagination v-if="bookings.last_page > 1" :paginator="bookings" />
    </div>
</template>
