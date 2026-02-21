<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SharedProps } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    staffUsers: Array<{
        id: number;
        name: string;
        email: string;
        created_at: string;
    }>;
}>();

const page = usePage<SharedProps>();
const currentUserId = computed(() => page.props.auth.user?.id);

const userToDelete = ref<{ id: number; name: string } | null>(null);

function confirmDelete(user: { id: number; name: string }) {
    userToDelete.value = user;
}

function deleteUser() {
    if (!userToDelete.value) return;
    router.delete(route('staff.users.destroy', userToDelete.value.id), {
        preserveScroll: true,
    });
    userToDelete.value = null;
}
</script>

<template>
    <Head title="Staff Accounts" />

    <PageHeader title="Staff Accounts" :subtitle="`${staffUsers.length} staff member${staffUsers.length === 1 ? '' : 's'}`">
        <template #actions>
            <Link
                :href="route('staff.users.create')"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Staff Member
            </Link>
        </template>
    </PageHeader>

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <table v-if="staffUsers.length > 0" class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Added</th>
                    <th class="relative px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                    v-for="user in staffUsers"
                    :key="user.id"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-xs font-bold text-indigo-700 dark:text-indigo-300">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ user.name }}
                                <span v-if="user.id === currentUserId" class="ml-1 text-xs text-zinc-400">(you)</span>
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ user.email }}</td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ new Date(user.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <ConfirmModal
                            v-if="user.id !== currentUserId"
                            title="Remove Staff Member"
                            :description="`Remove ${user.name} as a staff member? They will no longer be able to log in.`"
                            confirm-label="Remove"
                            :danger="true"
                            @confirm="deleteUser"
                        >
                            <button
                                type="button"
                                class="text-xs text-zinc-500 hover:text-red-600 transition-colors"
                                @click="confirmDelete(user)"
                            >
                                Remove
                            </button>
                        </ConfirmModal>
                        <span v-else class="text-xs text-zinc-300">—</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState
            v-else
            title="No staff members"
            description="Add team members to manage the kennel."
        >
            <template #action>
                <Link
                    :href="route('staff.users.create')"
                    class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors"
                >
                    Add Staff Member
                </Link>
            </template>
        </EmptyState>
    </div>
</template>
