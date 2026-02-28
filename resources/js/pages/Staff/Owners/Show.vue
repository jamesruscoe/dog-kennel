<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import { ref } from 'vue';
import type { Booking, Owner } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    owner: Owner;
    recentBookings: Booking[];
}>();

const deleteModal = ref<InstanceType<typeof ConfirmModal>>();

function deleteOwner() {
    router.delete(tenantRoute('staff.owners.destroy', props.owner.id));
}
</script>

<template>
    <Head :title="owner.name" />

    <PageHeader
        :title="owner.name"
        :breadcrumbs="[{ label: 'Owners', href: tenantRoute('staff.owners.index') }, { label: owner.name }]"
    >
        <template #actions>
            <Link
                :href="tenantRoute('staff.owners.edit', owner.id)"
                class="inline-flex items-center gap-2 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
            >
                Edit
            </Link>
            <ConfirmModal
                ref="deleteModal"
                title="Remove Owner"
                :description="`Remove ${owner.name} from the system? This action cannot be undone if they have no active bookings.`"
                confirm-label="Remove"
                :danger="true"
                @confirm="deleteOwner"
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
        <!-- Left: contact details -->
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Contact</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Email</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            <a :href="`mailto:${owner.email}`" class="hover:text-indigo-600">{{ owner.email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Phone</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ owner.phone || '—' }}</dd>
                    </div>
                    <div v-if="owner.address">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Address</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ owner.address }}</dd>
                    </div>
                </dl>
            </div>

            <div
                v-if="owner.emergency_contact_name || owner.emergency_contact_phone"
                class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6"
            >
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Emergency Contact</h2>
                <dl class="space-y-3 text-sm">
                    <div v-if="owner.emergency_contact_name">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Name</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ owner.emergency_contact_name }}</dd>
                    </div>
                    <div v-if="owner.emergency_contact_phone">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Phone</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">{{ owner.emergency_contact_phone }}</dd>
                    </div>
                </dl>
            </div>

            <div
                v-if="owner.notes"
                class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800/50 dark:bg-amber-900/20 p-6"
            >
                <h2 class="text-sm font-semibold text-amber-900 dark:text-amber-300 mb-2">Staff Notes</h2>
                <p class="text-sm text-amber-800 dark:text-amber-400 whitespace-pre-wrap">{{ owner.notes }}</p>
            </div>
        </div>

        <!-- Right: dogs + recent bookings -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dogs -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        Dogs
                        <span class="ml-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-xs text-zinc-500">{{ owner.dogs?.length ?? 0 }}</span>
                    </h2>
                    <Link
                        :href="tenantRoute('staff.dogs.create', { owner_id: owner.id })"
                        class="text-xs font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        + Add dog
                    </Link>
                </div>
                <ul v-if="owner.dogs && owner.dogs.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="dog in owner.dogs" :key="dog.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="tenantRoute('staff.dogs.show', dog.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ dog.name }}
                            </Link>
                            <p class="text-xs text-zinc-500">{{ dog.breed }}{{ dog.age_years ? ` · ${dog.age_years}yr` : '' }}</p>
                        </div>
                        <span class="text-xs text-zinc-400">
                            {{ dog.sex === 'male' ? '♂ Male' : '♀ Female' }}{{ dog.neutered ? ' · Neutered' : '' }}
                        </span>
                    </li>
                </ul>
                <EmptyState v-else title="No dogs yet" description="Add a dog for this owner to start booking." />
            </div>

            <!-- Recent bookings -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent Bookings</h2>
                </div>
                <ul v-if="recentBookings.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    <li v-for="booking in recentBookings" :key="booking.id" class="px-6 py-3 flex items-center justify-between">
                        <div>
                            <Link :href="tenantRoute('staff.bookings.show', booking.id)" class="text-sm font-medium text-zinc-900 dark:text-zinc-100 hover:text-indigo-600">
                                {{ booking.dog?.name ?? 'Unknown dog' }}
                            </Link>
                            <p class="text-xs text-zinc-500">
                                {{ new Date(booking.check_in_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short' }) }}
                                –
                                {{ new Date(booking.check_out_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                · {{ booking.nights }} night{{ booking.nights === 1 ? '' : 's' }}
                            </p>
                        </div>
                        <StatusBadge :status="booking.status" />
                    </li>
                </ul>
                <EmptyState v-else title="No bookings" description="No bookings have been made yet." />
            </div>
        </div>
    </div>
</template>
