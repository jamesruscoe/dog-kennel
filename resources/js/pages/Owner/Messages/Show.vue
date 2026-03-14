<script setup lang="ts">
import { useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, nextTick } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import type { Conversation, Message, Paginated, SharedProps } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();
const page = usePage<SharedProps>();
const authUserId = page.props.auth.user?.id;

const props = defineProps<{
    conversation: Conversation;
    messages: Paginated<Message>;
}>();

const allMessages = ref<Message[]>([...props.messages.data]);
const messagesEnd = ref<HTMLElement | null>(null);

function scrollToBottom() {
    nextTick(() => {
        messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
    });
}

onMounted(() => {
    scrollToBottom();

    router.post(tenantRoute('owner.messages.read', props.conversation.id), {}, { preserveScroll: true, preserveState: true });

    if (typeof window !== 'undefined' && (window as any).Echo) {
        (window as any).Echo.private(`conversation.${props.conversation.id}`)
            .listen('MessageSent', (e: Message) => {
                allMessages.value.push(e);
                scrollToBottom();
            });
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined' && (window as any).Echo) {
        (window as any).Echo.leave(`conversation.${props.conversation.id}`);
    }
});

const form = useForm({ body: '' });

function sendReply() {
    form.post(tenantRoute('owner.messages.reply', props.conversation.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            router.reload({ only: ['messages'], onSuccess: () => {
                allMessages.value = [...(page.props as any).messages.data];
                scrollToBottom();
            }});
        },
    });
}

function formatTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
    });
}

const otherPartyName = props.conversation.staff_user?.name ?? 'Unknown';
</script>

<template>
    <Head :title="`Messages — ${otherPartyName}`" />

    <PageHeader
        :title="otherPartyName"
        :breadcrumbs="[
            { label: 'Messages', href: tenantRoute('owner.messages.index') },
            { label: otherPartyName },
        ]"
    />

    <div class="flex flex-col h-[calc(100vh-220px)] rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div
                v-for="msg in allMessages"
                :key="msg.id"
                :class="[
                    'max-w-[75%] rounded-lg px-4 py-2.5',
                    msg.sender_id === authUserId
                        ? 'ml-auto bg-indigo-600 text-white'
                        : 'mr-auto bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100',
                ]"
            >
                <p class="text-xs font-medium mb-0.5" :class="msg.sender_id === authUserId ? 'text-indigo-200' : 'text-zinc-500'">
                    {{ msg.sender_name ?? 'Unknown' }}
                </p>
                <p class="text-sm whitespace-pre-wrap">{{ msg.body }}</p>
                <p class="text-[10px] mt-1" :class="msg.sender_id === authUserId ? 'text-indigo-300' : 'text-zinc-400'">
                    {{ formatTime(msg.created_at) }}
                </p>
            </div>
            <div ref="messagesEnd" />
        </div>

        <div class="border-t border-zinc-200 dark:border-zinc-700 p-4">
            <form class="flex gap-2" @submit.prevent="sendReply">
                <textarea
                    v-model="form.body"
                    rows="1"
                    placeholder="Type a message..."
                    class="flex-1 rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                    @keydown.enter.exact.prevent="sendReply"
                />
                <button
                    type="submit"
                    :disabled="form.processing || !form.body.trim()"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 transition-colors"
                >
                    Send
                </button>
            </form>
            <p v-if="form.errors.body" class="mt-1 text-xs text-red-600">{{ form.errors.body }}</p>
        </div>
    </div>
</template>
