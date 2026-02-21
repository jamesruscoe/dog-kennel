import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { Booking, BookingStatus } from '@/types/kennel';

/**
 * Lightweight UI state for the booking module.
 * Actual data fetching goes through Inertia (server-side props).
 * This store tracks local filter/sort state and optimistic status changes.
 */
export const useBookingStore = defineStore('bookings', () => {
    const filterStatus = ref<BookingStatus | 'all'>('all');
    const filterDogName = ref('');
    const sortBy = ref<'check_in_date' | 'created_at'>('check_in_date');
    const sortDir = ref<'asc' | 'desc'>('asc');

    // Optimistic status map: bookingId -> new status
    const pendingStatusChanges = ref<Record<number, BookingStatus>>({});

    function setFilter(status: BookingStatus | 'all') {
        filterStatus.value = status;
    }

    function setSearch(name: string) {
        filterDogName.value = name;
    }

    function markPendingChange(bookingId: number, status: BookingStatus) {
        pendingStatusChanges.value[bookingId] = status;
    }

    function clearPendingChange(bookingId: number) {
        delete pendingStatusChanges.value[bookingId];
    }

    function resolvedStatus(booking: Booking): BookingStatus {
        return pendingStatusChanges.value[booking.id] ?? booking.status;
    }

    return {
        filterStatus,
        filterDogName,
        sortBy,
        sortDir,
        pendingStatusChanges,
        setFilter,
        setSearch,
        markPendingChange,
        clearPendingChange,
        resolvedStatus,
    };
});
