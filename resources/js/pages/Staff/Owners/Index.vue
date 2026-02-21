<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import SearchFilter from '@/Components/Kennel/SearchFilter.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import { ref } from 'vue';
import type { Owner, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    owners: Paginated<Owner>;
    filters: { search?: string; active?: string };
}>();

const activeFilter = ref(props.filters.active === '1' || props.filters.active === 'true');

function toggleActive() {
    activeFilter.value = !activeFilter.value;
    router.get(
        route('staff.owners.index'),
        { ...props.filters, active: activeFilter.value ? '1' : undefined },
        { preserveState: true, replace: true },
    );
}

const deleteModal = ref<InstanceType<typeof ConfirmModal>>();
const ownerToDelete = ref<Owner | null>(null);

function confirmDelete(owner: Owner) {
    ownerToDelete.value = owner;
    deleteModal.value?.show();
}

function deleteOwner() {
    if (!ownerToDelete.value) return;
    router.delete(route('staff.owners.destroy', ownerToDelete.value.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Owners" />

    <PageHeader title="Owners" :subtitle="`${owners.total} owner${owners.total === 1 ? '' : 's'} registered`">
        <template #actions>
            <Link
                :href="route('staff.owners.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Owner
            </Link>
        </template>
    </PageHeader>

    <!-- Filters -->
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <SearchFilter
            route-name="staff.owners.index"
            :filters="filters"
            placeholder="Search name, email or phone…"
        />
        <button
            type="button"
            :class="[
                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                activeFilter
                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                    : 'border-zinc-300 bg-white text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
            ]"
            @click="toggleActive"
        >
            <span class="h-1.5 w-1.5 rounded-full" :class="activeFilter ? 'bg-indigo-500' : 'bg-zinc-400'" />
            Active bookings only
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <table v-if="owners.data.length > 0" class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Dogs</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Member since</th>
                    <th class="relative px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                    v-for="owner in owners.data"
                    :key="owner.id"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <td class="px-4 py-3">
                        <Link :href="route('staff.owners.show', owner.id)" class="group flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                                {{ owner.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600">{{ owner.name }}</p>
                                <p class="text-xs text-zinc-500">{{ owner.email }}</p>
                            </div>
                        </Link>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ owner.phone || '—' }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ owner.dogs_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ new Date(owner.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link
                                :href="route('staff.owners.edit', owner.id)"
                                class="text-xs text-zinc-500 hover:text-indigo-600 transition-colors"
                            >
                                Edit
                            </Link>
                            <ConfirmModal
                                ref="deleteModal"
                                title="Remove Owner"
                                :description="`Are you sure you want to remove ${owner.name}? This cannot be undone if they have no active bookings.`"
                                confirm-label="Remove"
                                :danger="true"
                                @confirm="deleteOwner"
                            >
                                <button
                                    type="button"
                                    class="text-xs text-zinc-500 hover:text-red-600 transition-colors"
                                    @click.stop="confirmDelete(owner)"
                                >
                                    Remove
                                </button>
                            </ConfirmModal>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState
            v-else
            title="No owners found"
            :description="filters.search ? `No results for '${filters.search}'.` : 'Add the first owner to get started.'"
        >
            <template #action>
                <Link
                    :href="route('staff.owners.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    Add Owner
                </Link>
            </template>
        </EmptyState>

        <Pagination v-if="owners.last_page > 1" :paginator="owners" />
    </div>
</template>
