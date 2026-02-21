<script setup lang="ts">
import type { BookingStatus } from '@/types/kennel';

defineProps<{
    status: BookingStatus;
    label?: string;
}>();

const colourMap: Record<BookingStatus, string> = {
    pending:   'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
    approved:  'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    rejected:  'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    cancelled: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
};

const labelMap: Record<BookingStatus, string> = {
    pending:   'Pending',
    approved:  'Approved',
    rejected:  'Rejected',
    cancelled: 'Cancelled',
    completed: 'Completed',
};
</script>

<template>
    <span
        :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', colourMap[status]]"
    >
        {{ label ?? labelMap[status] }}
    </span>
</template>
