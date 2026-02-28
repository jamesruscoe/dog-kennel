<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import type { Booking } from '@/types/kennel';
import { useTenantRoute } from '@/composables/useTenantRoute';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{ booking: Booking }>();

const ACTIVITY_TYPES = [
    { value: 'feeding',      label: 'Feeding' },
    { value: 'walking',      label: 'Walking' },
    { value: 'medication',   label: 'Medication' },
    { value: 'grooming',     label: 'Grooming' },
    { value: 'play',         label: 'Play' },
    { value: 'toilet',       label: 'Toilet' },
    { value: 'health_check', label: 'Health Check' },
    { value: 'other',        label: 'Other' },
];

// Default occurred_at to now (local datetime-local format: YYYY-MM-DDTHH:mm)
function nowLocal(): string {
    const d = new Date();
    d.setSeconds(0, 0);
    return d.toISOString().slice(0, 16);
}

const form = useForm({
    activity_type: 'feeding',
    notes:         '',
    occurred_at:   nowLocal(),
});

function submit() {
    form.post(tenantRoute('staff.care-logs.store', props.booking.id), {
        onSuccess: () => form.reset('notes'),
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

const ACTIVITY_COLORS: Record<string, string> = {
    feeding:      'bg-orange-100 text-orange-700',
    walking:      'bg-blue-100 text-blue-700',
    medication:   'bg-red-100 text-red-700',
    grooming:     'bg-pink-100 text-pink-700',
    play:         'bg-green-100 text-green-700',
    toilet:       'bg-yellow-100 text-yellow-700',
    health_check: 'bg-purple-100 text-purple-700',
    other:        'bg-zinc-100 text-zinc-600',
};
</script>

<template>
    <Head :title="`Log Activity — Booking #${booking.id}`" />

    <PageHeader
        :title="`Log Activity`"
        :breadcrumbs="[
            { label: 'Bookings', href: tenantRoute('staff.bookings.index') },
            { label: `#${booking.id}`, href: tenantRoute('staff.bookings.show', booking.id) },
            { label: 'Log Activity' },
        ]"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 max-w-4xl">
        <!-- Form -->
        <div class="lg:col-span-2">
            <form class="space-y-5" @submit.prevent="submit">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-5">

                    <!-- Activity type -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Activity Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="t in ACTIVITY_TYPES"
                                :key="t.value"
                                type="button"
                                :class="[
                                    'rounded-lg border px-2 py-2.5 text-xs font-medium text-center transition-colors',
                                    form.activity_type === t.value
                                        ? `${ACTIVITY_COLORS[t.value]} border-transparent ring-2 ring-offset-1 ring-indigo-500`
                                        : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-indigo-300',
                                ]"
                                @click="form.activity_type = t.value"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                        <p v-if="form.errors.activity_type" class="mt-1 text-xs text-red-600">{{ form.errors.activity_type }}</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Describe what happened — amount eaten, distance walked, any observations…"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Occurred at -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            When did this occur?
                        </label>
                        <input
                            v-model="form.occurred_at"
                            type="datetime-local"
                            class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p class="mt-1 text-xs text-zinc-400">Defaults to now. Adjust if logging retrospectively.</p>
                        <p v-if="form.errors.occurred_at" class="mt-1 text-xs text-red-600">{{ form.errors.occurred_at }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="tenantRoute('staff.bookings.show', booking.id)"
                        class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        Save Log Entry
                    </button>
                </div>
            </form>
        </div>

        <!-- Booking context sidebar -->
        <div class="lg:col-span-1">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 space-y-3 sticky top-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking #{{ booking.id }}</h3>
                    <StatusBadge :status="booking.status" />
                </div>
                <dl class="space-y-2 text-sm">
                    <div v-if="booking.dog">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Dog</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ booking.dog.name }}</dd>
                    </div>
                    <div v-if="booking.dog?.owner">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Owner</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ booking.dog.owner.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Stay</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">
                            {{ formatDate(booking.check_in_date) }} – {{ formatDate(booking.check_out_date) }}
                        </dd>
                    </div>
                </dl>
                <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3">
                    <p class="text-xs text-zinc-400">
                        {{ booking.care_logs_count ?? 0 }} log entr{{ (booking.care_logs_count ?? 0) === 1 ? 'y' : 'ies' }} so far
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
