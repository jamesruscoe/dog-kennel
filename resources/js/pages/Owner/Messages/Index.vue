<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import type { Conversation, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

defineProps<{
    conversations: Paginated<Conversation>;
}>();

function formatDate(d: string | null) {
    if (!d) return '—';
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
    });
}
</script>

<template>
    <Head title="Messages" />

    <PageHeader title="Messages" subtitle="Conversations with staff" />

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <ul v-if="conversations.data.length > 0" class="divide-y divide-zinc-100 dark:divide-zinc-800">
            <li v-for="c in conversations.data" :key="c.id">
                <Link
                    :href="tenantRoute('owner.messages.show', c.id)"
                    class="flex items-center justify-between px-5 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ c.staff_user?.name ?? 'Unknown' }}
                            </p>
                            <span
                                v-if="c.unread_count > 0"
                                class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-indigo-600 px-1.5 text-[10px] font-bold text-white"
                            >
                                {{ c.unread_count }}
                            </span>
                        </div>
                        <p v-if="c.latest_message" class="mt-0.5 text-sm text-zinc-500 truncate">
                            {{ c.latest_message.body }}
                        </p>
                    </div>
                    <span class="ml-4 shrink-0 text-xs text-zinc-400">
                        {{ formatDate(c.last_message_at) }}
                    </span>
                </Link>
            </li>
        </ul>

        <EmptyState
            v-else
            title="No messages yet"
            description="You'll see messages here when staff reach out to you."
        />

        <Pagination v-if="conversations.last_page > 1" :paginator="conversations" />
    </div>
</template>
