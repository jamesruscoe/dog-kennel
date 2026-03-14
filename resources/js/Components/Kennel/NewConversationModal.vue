<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useTenantRoute } from '@/composables/useTenantRoute';

const props = defineProps<{
    owners: Array<{ id: number; name: string }>;
}>();

const emit = defineEmits<{ close: [] }>();

const tenantRoute = useTenantRoute();

const form = useForm({
    owner_user_id: '' as number | '',
    body: '',
});

const search = ref('');
const filteredOwners = computed(() => {
    if (!search.value) return props.owners;
    const q = search.value.toLowerCase();
    return props.owners.filter((o) => o.name.toLowerCase().includes(q));
});

function submit() {
    if (!form.owner_user_id || !form.body) return;
    form.post(tenantRoute('staff.messages.store'), {
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="emit('close')">
            <div class="w-full max-w-md rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-xl">
                <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 px-5 py-4">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">New Message</h3>
                    <button type="button" class="text-zinc-400 hover:text-zinc-600" @click="emit('close')">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form class="p-5 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">To (Owner)</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search owners..."
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-2"
                        />
                        <select
                            v-model="form.owner_user_id"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            size="4"
                        >
                            <option v-for="o in filteredOwners" :key="o.id" :value="o.id">{{ o.name }}</option>
                        </select>
                        <p v-if="form.errors.owner_user_id" class="mt-1 text-xs text-red-600">{{ form.errors.owner_user_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Message</label>
                        <textarea
                            v-model="form.body"
                            rows="4"
                            placeholder="Type your message..."
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.body" class="mt-1 text-xs text-red-600">{{ form.errors.body }}</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700"
                            @click="emit('close')"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.owner_user_id || !form.body"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
