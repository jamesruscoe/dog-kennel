<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import { ref } from 'vue';
import type { Booking, Dog } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    dog: Dog;
    bookings: Booking[];
}>();

const deleteModal = ref<InstanceType<typeof ConfirmModal>>();

function deleteDog() {
    router.delete(tenantRoute('staff.dogs.destroy', props.dog.id));
}
</script>

<template>
    <Head :title="dog.name" />

    <PageHeader
        :title="dog.name"
        :subtitle="dog.breed"
        :breadcrumbs="[
            { label: 'Dogs', href: tenantRoute('staff.dogs.index') },
            { label: dog.name },
        ]"
    >
        <template #actions>
            <Link
                :href="tenantRoute('staff.dogs.edit', dog.id)"
                class="inline-flex items-center gap-2 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
            >
                Edit
            </Link>
            <ConfirmModal
                ref="deleteModal"
                title="Remove Dog"
                :description="`Remove ${dog.name} from the system? This cannot be undone if they have no active bookings.`"
                confirm-label="Remove"
                :danger="true"
                @confirm="deleteDog"
            >
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border border-red-300 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                >
                    Remove
                </button>
            </ConfirmModal>
        </template>
    </PageHeader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left: dog profile -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Identity -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Profile</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Owner</dt>
                        <dd class="mt-0.5">
                            <Link
                                v-if="dog.owner"
                                :href="tenantRoute('staff.owners.show', dog.owner.id)"
                                class="text-zinc-800 dark:text-zinc-200 hover:text-indigo-600"
                            >
                                {{ dog.owner.name }}
                            </Link>
                            <span v-else class="text-zinc-400">—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Breed</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.breed }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Sex</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            {{ dog.sex === 'male' ? '♂ Male' : '♀ Female' }}
                            {{ dog.neutered ? '(Neutered)' : '' }}
                        </dd>
                    </div>
                    <div v-if="dog.date_of_birth">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Age</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            {{ dog.age_years }} year{{ dog.age_years === 1 ? '' : 's' }}
                            ({{ new Date(dog.date_of_birth).toLocaleDateString('en-GB') }})
                        </dd>
                    </div>
                    <div v-if="dog.weight_kg">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Weight</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.weight_kg }} kg</dd>
                    </div>
                    <div v-if="dog.colour">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Colour</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.colour }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Vaccination & ID -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Vet &amp; ID</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Vaccinations</dt>
                        <dd class="mt-0.5 flex items-center gap-1.5">
                            <span
                                :class="dog.vaccination_confirmed ? 'text-green-600' : 'text-amber-600'"
                                class="text-sm font-medium"
                            >
                                {{ dog.vaccination_confirmed ? '✓ Confirmed' : '⚠ Not confirmed' }}
                            </span>
                        </dd>
                    </div>
                    <div v-if="dog.microchip_number">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Microchip</dt>
                        <dd class="mt-0.5 font-mono text-xs text-zinc-800 dark:text-zinc-200">{{ dog.microchip_number }}</dd>
                    </div>
                    <div v-if="dog.vet_name">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Vet Practice</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.vet_name }}</dd>
                    </div>
                    <div v-if="dog.vet_phone">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Vet Phone</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ dog.vet_phone }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Right: notes + bookings -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Care notes -->
            <div
                v-if="dog.medical_notes || dog.dietary_notes || dog.behavioural_notes"
                class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6"
            >
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Care Notes</h2>
                <div class="space-y-4">
                    <div v-if="dog.medical_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Medical</h3>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.medical_notes }}</p>
                    </div>
                    <div v-if="dog.dietary_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Dietary</h3>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.dietary_notes }}</p>
                    </div>
                    <div v-if="dog.behavioural_notes">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-1">Behavioural</h3>
                        <p class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ dog.behavioural_notes }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking history -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking History</h2>
                    <Link
                        :href="tenantRoute('staff.bookings.create', { dog_id: dog.id })"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        + New booking
                    </Link>
                </div>
                <ul v-if="bookings.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="booking in bookings" :key="booking.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="tenantRoute('staff.bookings.show', booking.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ new Date(booking.check_in_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                –
                                {{ new Date(booking.check_out_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </Link>
                            <p class="text-xs text-zinc-500">
                                {{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}
                                <template v-if="booking.care_logs_count !== undefined">
                                    · {{ booking.care_logs_count }} care log{{ booking.care_logs_count === 1 ? '' : 's' }}
                                </template>
                            </p>
                        </div>
                        <StatusBadge :status="booking.status" />
                    </li>
                </ul>
                <EmptyState v-else title="No bookings" description="This dog hasn't been booked in yet." />
            </div>
        </div>
    </div>
</template>
