<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import SearchFilter from '@/Components/Kennel/SearchFilter.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import { ref } from 'vue';
import type { Dog, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    dogs: Paginated<Dog>;
    filters: { search?: string; breed?: string; owner_id?: string };
}>();

const dogToDelete = ref<Dog | null>(null);

function confirmDelete(dog: Dog) {
    dogToDelete.value = dog;
}

function deleteDog() {
    if (!dogToDelete.value) return;
    router.delete(tenantRoute('staff.dogs.destroy', dogToDelete.value.id), {
        preserveScroll: true,
    });
    dogToDelete.value = null;
}
</script>

<template>
    <Head title="Dogs" />

    <PageHeader title="Dogs" :subtitle="`${dogs.total} dog${dogs.total === 1 ? '' : 's'} registered`">
        <template #actions>
            <Link
                :href="tenantRoute('staff.dogs.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Dog
            </Link>
        </template>
    </PageHeader>

    <!-- Filters -->
    <div class="mb-4">
        <SearchFilter
            route-name="staff.dogs.index"
            :filters="filters"
            placeholder="Search by name…"
        />
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <table v-if="dogs.data.length > 0" class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Dog</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Owner</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Sex</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Bookings</th>
                    <th class="relative px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                    v-for="dog in dogs.data"
                    :key="dog.id"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <td class="px-4 py-3">
                        <Link :href="tenantRoute('staff.dogs.show', dog.id)" class="group">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600">{{ dog.name }}</p>
                            <p class="text-xs text-zinc-500">{{ dog.breed }}{{ dog.age_years ? ` · ${dog.age_years}yr` : '' }}</p>
                        </Link>
                    </td>
                    <td class="px-4 py-3">
                        <Link
                            v-if="dog.owner"
                            :href="tenantRoute('staff.owners.show', dog.owner.id)"
                            class="text-sm text-zinc-700 dark:text-zinc-300 hover:text-indigo-600"
                        >
                            {{ dog.owner.name }}
                        </Link>
                        <span v-else class="text-sm text-zinc-400">—</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ dog.sex === 'male' ? '♂ Male' : '♀ Female' }}{{ dog.neutered ? ' · N' : '' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ dog.bookings_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <Link :href="tenantRoute('staff.dogs.edit', dog.id)" class="text-xs text-zinc-500 hover:text-indigo-600 transition-colors">Edit</Link>
                            <ConfirmModal
                                title="Remove Dog"
                                :description="`Remove ${dog.name} from the system? This cannot be undone if they have no active bookings.`"
                                confirm-label="Remove"
                                :danger="true"
                                @confirm="deleteDog"
                            >
                                <button
                                    type="button"
                                    class="text-xs text-zinc-500 hover:text-red-600 transition-colors"
                                    @click="confirmDelete(dog)"
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
            title="No dogs found"
            :description="filters.search ? `No results for '${filters.search}'.` : 'Add the first dog to get started.'"
        >
            <template #action>
                <Link
                    :href="tenantRoute('staff.dogs.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    Add Dog
                </Link>
            </template>
        </EmptyState>

        <Pagination v-if="dogs.last_page > 1" :paginator="dogs" />
    </div>
</template>
