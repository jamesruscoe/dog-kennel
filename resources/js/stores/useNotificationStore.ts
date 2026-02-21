import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { AppNotification } from '@/types/kennel';
import axios from 'axios';

export const useNotificationStore = defineStore('notifications', () => {
    const notifications = ref<AppNotification[]>([]);
    const loading = ref(false);

    const unreadCount = computed(
        () => notifications.value.filter((n) => !n.read_at).length,
    );

    const unread = computed(() => notifications.value.filter((n) => !n.read_at));

    async function fetch() {
        loading.value = true;
        try {
            const { data } = await axios.get('/notifications');
            notifications.value = data.notifications;
        } finally {
            loading.value = false;
        }
    }

    async function markRead(id: string) {
        await axios.patch(`/notifications/${id}/read`);
        const n = notifications.value.find((n) => n.id === id);
        if (n) n.read_at = new Date().toISOString();
    }

    async function markAllRead() {
        await axios.patch('/notifications/read-all');
        notifications.value.forEach((n) => {
            n.read_at = n.read_at ?? new Date().toISOString();
        });
    }

    function pushNotification(notification: AppNotification) {
        notifications.value.unshift(notification);
    }

    return {
        notifications,
        loading,
        unreadCount,
        unread,
        fetch,
        markRead,
        markAllRead,
        pushNotification,
    };
});
