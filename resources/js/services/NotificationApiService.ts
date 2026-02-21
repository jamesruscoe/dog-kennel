import axios from 'axios';
import type { AppNotification } from '@/types/kennel';

export const NotificationApiService = {
    async fetchUnread(): Promise<AppNotification[]> {
        const { data } = await axios.get('/notifications');
        return data.notifications;
    },

    async markRead(id: string): Promise<void> {
        await axios.patch(`/notifications/${id}/read`);
    },

    async markAllRead(): Promise<void> {
        await axios.patch('/notifications/read-all');
    },
};
