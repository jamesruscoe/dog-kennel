import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ActivityType } from '@/types/kennel';

export const useCareLogStore = defineStore('careLogs', () => {
    const filterActivity = ref<ActivityType | 'all'>('all');
    const filterDate = ref<string>(''); // Y-m-d

    function setActivityFilter(value: ActivityType | 'all') {
        filterActivity.value = value;
    }

    function setDateFilter(value: string) {
        filterDate.value = value;
    }

    function reset() {
        filterActivity.value = 'all';
        filterDate.value = '';
    }

    return { filterActivity, filterDate, setActivityFilter, setDateFilter, reset };
});
