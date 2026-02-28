<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import { ref } from 'vue';
import type { Booking } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{ booking: Booking }>();

// Reject form
const rejectModal = ref<InstanceType<typeof ConfirmModal>>();
const rejectForm = useForm({ rejection_reason: '' });

function submitReject() {
    rejectForm.patch(tenantRoute('staff.bookings.reject', props.booking.id), {
        onSuccess: () => rejectModal.value?.hide(),
    });
}

// Cancel form
const cancelModal = ref<InstanceType<typeof ConfirmModal>>();
const cancelForm = useForm({ cancellation_reason: '' });

function submitCancel() {
    cancelForm.patch(tenantRoute('staff.bookings.cancel', props.booking.id), {
        onSuccess: () => cancelModal.value?.hide(),
    });
}

function approve() {
    router.patch(tenantRoute('staff.bookings.approve', props.booking.id));
}

function complete() {
    router.patch(tenantRoute('staff.bookings.complete', props.booking.id));
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const ACTIVITY_LABELS: Record<string, string> = {
    feeding: 'Feeding', walking: 'Walking', medication: 'Medication',
    grooming: 'Grooming', play: 'Play', toilet: 'Toilet',
    health_check: 'Health Check', other: 'Other',
};
</script>

<template>
    <Head :title="`Booking #${booking.id}`" />

    <PageHeader
        :title="`Booking #${booking.id}`"
        :breadcrumbs="[{ label: 'Bookings', href: tenantRoute('staff.bookings.index') }, { label: `#${booking.id}` }]"
    >
        <template #actions>
            <!-- Approve (pending only) -->
            <button
                v-if="booking.status === 'pending'"
                type="button"
                class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors"
                @click="approve"
            >
                Approve
            </button>

            <!-- Reject (pending only) -->
            <ConfirmModal
                v-if="booking.status === 'pending'"
                ref="rejectModal"
                title="Reject Booking"
                description="Provide a reason for rejecting this booking."
                confirm-label="Reject"
                :danger="true"
                @confirm="submitReject"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                >
                    Reject
                </button>
                <template #body>
                    <textarea
                        v-model="rejectForm.rejection_reason"
                        rows="3"
                        placeholder="Reason (optional)…"
                        class="mt-3 w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </template>
            </ConfirmModal>

            <!-- Complete (approved only) -->
            <button
                v-if="booking.status === 'approved'"
                type="button"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                @click="complete"
            >
                Mark Complete
            </button>

            <!-- Cancel (pending or approved) -->
            <ConfirmModal
                v-if="booking.status === 'pending' || booking.status === 'approved'"
                ref="cancelModal"
                title="Cancel Booking"
                description="Are you sure you want to cancel this booking?"
                confirm-label="Cancel Booking"
                :danger="true"
                @confirm="submitCancel"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                >
                    Cancel
                </button>
                <template #body>
                    <textarea
                        v-model="cancelForm.cancellation_reason"
                        rows="3"
                        placeholder="Reason (optional)…"
                        class="mt-3 w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </template>
            </ConfirmModal>
        </template>
    </PageHeader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: booking details -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status card -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Status</h2>
                    <StatusBadge :status="booking.status" />
                </div>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Check-in</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ formatDate(booking.check_in_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Check-out</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ formatDate(booking.check_out_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Duration</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}</dd>
                    </div>
                    <div v-if="booking.amount_display">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Amount</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200 font-medium">{{ booking.amount_display }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Dog card -->
            <div v-if="booking.dog" class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Dog</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Name</dt>
                        <dd class="mt-0.5">
                            <Link :href="tenantRoute('staff.dogs.show', booking.dog.id)" class="text-zinc-800 dark:text-zinc-200 hover:text-indigo-600">
                                {{ booking.dog.name }}
                            </Link>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Breed</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ booking.dog.breed }}</dd>
                    </div>
                    <div v-if="booking.dog.owner">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Owner</dt>
                        <dd class="mt-0.5">
                            <Link :href="tenantRoute('staff.owners.show', booking.dog.owner.id)" class="text-zinc-800 dark:text-zinc-200 hover:text-indigo-600">
                                {{ booking.dog.owner.name }}
                            </Link>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Notes -->
            <div v-if="booking.notes || booking.special_requirements" class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800/50 dark:bg-amber-900/20 p-6">
                <h2 class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-2">Notes</h2>
                <p v-if="booking.notes" class="text-sm text-amber-800 dark:text-amber-400 whitespace-pre-wrap mb-2">{{ booking.notes }}</p>
                <div v-if="booking.special_requirements">
                    <p class="text-xs font-medium text-amber-700 dark:text-amber-500 uppercase tracking-wide mb-1">Special Requirements</p>
                    <p class="text-sm text-amber-800 dark:text-amber-400 whitespace-pre-wrap">{{ booking.special_requirements }}</p>
                </div>
            </div>

            <!-- Rejection / cancellation reason -->
            <div
                v-if="booking.rejection_reason || booking.cancellation_reason"
                class="rounded-lg border border-red-200 bg-red-50 dark:border-red-800/50 dark:bg-red-900/20 p-6"
            >
                <h2 class="text-sm font-semibold text-red-800 dark:text-red-300 mb-2">
                    {{ booking.rejection_reason ? 'Rejection Reason' : 'Cancellation Reason' }}
                </h2>
                <p class="text-sm text-red-700 dark:text-red-400 whitespace-pre-wrap">
                    {{ booking.rejection_reason ?? booking.cancellation_reason }}
                </p>
            </div>
        </div>

        <!-- Right: care log feed -->
        <div class="lg:col-span-2">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        Care Activity Log
                        <span class="ml-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-xs text-zinc-500">
                            {{ booking.care_logs?.length ?? booking.care_logs_count ?? 0 }}
                        </span>
                    </h2>
                    <Link
                        v-if="booking.status === 'approved'"
                        :href="tenantRoute('staff.care-logs.create', booking.id)"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
                    >
                        + Log activity
                    </Link>
                </div>

                <ul v-if="booking.care_logs && booking.care_logs.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="log in booking.care_logs" :key="log.id" class="px-6 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ ACTIVITY_LABELS[log.activity_type] ?? log.activity_type }}
                                </p>
                                <p v-if="log.notes" class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-wrap">{{ log.notes }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs text-zinc-500">{{ formatDateTime(log.occurred_at) }}</p>
                                <p v-if="log.logged_by_user" class="text-xs text-zinc-400">by {{ log.logged_by_user.name }}</p>
                            </div>
                        </div>
                    </li>
                </ul>

                <EmptyState
                    v-else
                    title="No activity logged"
                    description="Care activity will appear here once the booking is active."
                />
            </div>
        </div>
    </div>
</template>
