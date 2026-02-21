<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import type { Dog, KennelSettings } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    dogs: Dog[];
    settings: KennelSettings;
}>();

const form = useForm({
    dog_id:               '',
    check_in_date:        '',
    check_out_date:       '',
    notes:                '',
    special_requirements: '',
});

const nights = computed(() => {
    if (!form.check_in_date || !form.check_out_date) return 0;
    const ms = new Date(form.check_out_date).getTime() - new Date(form.check_in_date).getTime();
    return Math.max(0, Math.round(ms / 86_400_000));
});

const estimatedTotal = computed(() => {
    if (!nights.value) return null;
    const pence = nights.value * props.settings.nightly_rate_pence;
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(pence / 100);
});

const minCheckIn = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() + props.settings.booking_lead_days);
    return d.toISOString().substring(0, 10);
});

const minCheckOut = computed(() => {
    if (!form.check_in_date) return minCheckIn.value;
    const d = new Date(form.check_in_date);
    d.setDate(d.getDate() + 1);
    return d.toISOString().substring(0, 10);
});

function submit() {
    form.post(route('staff.bookings.store'));
}
</script>

<template>
    <Head title="New Booking" />

    <PageHeader
        title="New Booking"
        :breadcrumbs="[{ label: 'Bookings', href: route('staff.bookings.index') }, { label: 'New' }]"
    />

    <div class="max-w-2xl">
        <form class="space-y-6" @submit.prevent="submit">
            <!-- Validation error -->
            <div
                v-if="form.errors.booking"
                class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-sm text-red-700 dark:text-red-400"
            >
                {{ form.errors.booking }}
            </div>

            <!-- Dog selector -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Dog</h2>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Select Dog <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.dog_id"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">— choose a dog —</option>
                        <option v-for="dog in dogs" :key="dog.id" :value="dog.id">
                            {{ dog.name }} ({{ dog.breed }}){{ dog.owner ? ` — ${dog.owner.name}` : '' }}
                        </option>
                    </select>
                    <p v-if="form.errors.dog_id" class="mt-1 text-xs text-red-600">{{ form.errors.dog_id }}</p>
                </div>
            </div>

            <!-- Dates -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Dates</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Check-in <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.check_in_date"
                            type="date"
                            :min="minCheckIn"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.check_in_date" class="mt-1 text-xs text-red-600">{{ form.errors.check_in_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Check-out <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.check_out_date"
                            type="date"
                            :min="minCheckOut"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.check_out_date" class="mt-1 text-xs text-red-600">{{ form.errors.check_out_date }}</p>
                    </div>
                </div>

                <!-- Cost preview -->
                <div v-if="nights > 0" class="rounded-md bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 px-4 py-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-indigo-700 dark:text-indigo-300">
                            {{ nights }} night{{ nights === 1 ? '' : 's' }} × {{ settings.nightly_rate_display }}
                        </span>
                        <span class="font-semibold text-indigo-900 dark:text-indigo-100">{{ estimatedTotal }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Notes</h2>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Special Requirements</label>
                    <textarea
                        v-model="form.special_requirements"
                        rows="3"
                        class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link
                    :href="route('staff.bookings.index')"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                >
                    Create Booking
                </button>
            </div>
        </form>
    </div>
</template>
