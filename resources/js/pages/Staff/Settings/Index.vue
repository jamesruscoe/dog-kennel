<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import type { KennelSettings } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{ settings: KennelSettings }>();

const DAYS = [
    { value: 1, label: 'Mon' },
    { value: 2, label: 'Tue' },
    { value: 3, label: 'Wed' },
    { value: 4, label: 'Thu' },
    { value: 5, label: 'Fri' },
    { value: 6, label: 'Sat' },
    { value: 7, label: 'Sun' },
];

const form = useForm({
    max_capacity:         props.settings.max_capacity,
    nightly_rate_pounds:  (props.settings.nightly_rate_pence / 100).toFixed(2),
    operating_days:       [...props.settings.operating_days] as number[],
    check_in_time:        props.settings.check_in_time,
    check_out_time:       props.settings.check_out_time,
    booking_lead_days:    props.settings.booking_lead_days,
    terms_and_conditions: props.settings.terms_and_conditions ?? '',
});

const ratePreview = computed(() => {
    const pence = Math.round(parseFloat(form.nightly_rate_pounds) * 100) || 0;
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(pence / 100);
});

function toggleDay(day: number) {
    const idx = form.operating_days.indexOf(day);
    if (idx === -1) {
        form.operating_days.push(day);
        form.operating_days.sort((a, b) => a - b);
    } else {
        form.operating_days.splice(idx, 1);
    }
}

function submit() {
    const pence = Math.round(parseFloat(form.nightly_rate_pounds) * 100);
    form
        .transform(data => ({
            max_capacity:         data.max_capacity,
            nightly_rate_pence:   pence,
            operating_days:       data.operating_days,
            check_in_time:        data.check_in_time,
            check_out_time:       data.check_out_time,
            booking_lead_days:    data.booking_lead_days,
            terms_and_conditions: data.terms_and_conditions || null,
        }))
        .patch(route('staff.settings.update'));
}
</script>

<template>
    <Head title="Kennel Settings" />

    <PageHeader title="Kennel Settings" subtitle="Configure capacity, pricing and operating schedule." />

    <form class="space-y-6 max-w-2xl" @submit.prevent="submit">

        <!-- Capacity & Pricing -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-5">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Capacity &amp; Pricing</h2>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Max Capacity</label>
                    <div class="relative">
                        <input
                            v-model.number="form.max_capacity"
                            type="number"
                            min="1"
                            max="500"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 pr-10 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <span class="pointer-events-none absolute right-3 top-2 text-xs text-zinc-400">dogs</span>
                    </div>
                    <p v-if="form.errors.max_capacity" class="mt-1 text-xs text-red-600">{{ form.errors.max_capacity }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nightly Rate</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-2 text-sm text-zinc-400">£</span>
                        <input
                            v-model="form.nightly_rate_pounds"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 pl-7 pr-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <p class="mt-1 text-xs text-zinc-400">Stored as {{ ratePreview }} per night</p>
                    <p v-if="form.errors.nightly_rate_pence" class="mt-1 text-xs text-red-600">{{ form.errors.nightly_rate_pence }}</p>
                </div>
            </div>
        </div>

        <!-- Operating Schedule -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-5">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Operating Schedule</h2>

            <!-- Operating days toggle buttons -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Operating Days</label>
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-for="day in DAYS"
                        :key="day.value"
                        type="button"
                        :class="[
                            'w-12 py-1.5 rounded-full text-sm font-medium border transition-colors',
                            form.operating_days.includes(day.value)
                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                : 'bg-white dark:bg-zinc-800 border-zinc-300 dark:border-zinc-600 text-zinc-600 dark:text-zinc-400 hover:border-indigo-400',
                        ]"
                        @click="toggleDay(day.value)"
                    >
                        {{ day.label }}
                    </button>
                </div>
                <p v-if="form.errors.operating_days" class="mt-1 text-xs text-red-600">{{ form.errors.operating_days }}</p>
            </div>

            <!-- Check-in / check-out times -->
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Check-in Time</label>
                    <input
                        v-model="form.check_in_time"
                        type="time"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.check_in_time" class="mt-1 text-xs text-red-600">{{ form.errors.check_in_time }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Check-out Time</label>
                    <input
                        v-model="form.check_out_time"
                        type="time"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.check_out_time" class="mt-1 text-xs text-red-600">{{ form.errors.check_out_time }}</p>
                </div>
            </div>

            <!-- Booking lead days -->
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Booking Lead Time</label>
                <div class="flex items-center gap-3">
                    <input
                        v-model.number="form.booking_lead_days"
                        type="number"
                        min="0"
                        max="90"
                        class="w-28 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <span class="text-sm text-zinc-500">days in advance</span>
                </div>
                <p class="mt-1 text-xs text-zinc-400">Set to 0 to allow same-day bookings.</p>
                <p v-if="form.errors.booking_lead_days" class="mt-1 text-xs text-red-600">{{ form.errors.booking_lead_days }}</p>
            </div>
        </div>

        <!-- Terms & Conditions -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-3">
            <div>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Terms &amp; Conditions</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Shown to owners when they request a booking.</p>
            </div>
            <textarea
                v-model="form.terms_and_conditions"
                rows="6"
                placeholder="Enter your kennel's terms and conditions…"
                class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>

        <!-- Footer: last updated + save -->
        <div class="flex items-center justify-between">
            <p class="text-xs text-zinc-400">
                Last updated:
                {{ settings.updated_at ? new Date(settings.updated_at).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) : '—' }}
            </p>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
            >
                Save Settings
            </button>
        </div>
    </form>
</template>
