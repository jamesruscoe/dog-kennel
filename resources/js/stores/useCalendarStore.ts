import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { CalendarDay, KennelSettings } from '@/types/kennel';
import axios from 'axios';

export const useCalendarStore = defineStore('calendar', () => {
    const year = ref(new Date().getFullYear());
    const month = ref(new Date().getMonth() + 1); // 1-indexed
    const occupancy = ref<Record<string, number>>({});
    const maxCapacity = ref(0);
    const settings = ref<KennelSettings | null>(null);
    const loading = ref(false);

    const days = computed<CalendarDay[]>(() => {
        const result: CalendarDay[] = [];
        const firstDay = new Date(year.value, month.value - 1, 1);
        const daysInMonth = new Date(year.value, month.value, 0).getDate();
        const operatingDays = settings.value?.operating_days ?? [];
        const today = new Date().toISOString().slice(0, 10);

        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(year.value, month.value - 1, d);
            const dateStr = date.toISOString().slice(0, 10);
            // JS getDay() is 0=Sun...6=Sat; convert to 1=Mon...7=Sun
            const dayOfWeek = date.getDay() === 0 ? 7 : date.getDay();
            const isOperating = operatingDays.includes(dayOfWeek);
            const occ = occupancy.value[dateStr] ?? 0;

            result.push({
                date: dateStr,
                isOperating,
                occupancy: occ,
                capacity: maxCapacity.value,
                isFull: occ >= maxCapacity.value,
                isToday: dateStr === today,
                isPast: dateStr < today,
            });
        }

        return result;
    });

    const monthLabel = computed(() => {
        return new Date(year.value, month.value - 1).toLocaleDateString('en-GB', {
            month: 'long',
            year: 'numeric',
        });
    });

    async function loadMonth(y?: number, m?: number) {
        if (y !== undefined) year.value = y;
        if (m !== undefined) month.value = m;

        loading.value = true;
        try {
            const { data } = await axios.get('/staff/calendar/occupancy', {
                params: { year: year.value, month: month.value },
            });
            occupancy.value = data.occupancy;
            maxCapacity.value = data.max_capacity;
        } finally {
            loading.value = false;
        }
    }

    function nextMonth() {
        if (month.value === 12) {
            month.value = 1;
            year.value++;
        } else {
            month.value++;
        }
        loadMonth();
    }

    function prevMonth() {
        if (month.value === 1) {
            month.value = 12;
            year.value--;
        } else {
            month.value--;
        }
        loadMonth();
    }

    function setSettings(s: KennelSettings) {
        settings.value = s;
        maxCapacity.value = s.max_capacity;
    }

    return {
        year,
        month,
        occupancy,
        maxCapacity,
        settings,
        loading,
        days,
        monthLabel,
        loadMonth,
        nextMonth,
        prevMonth,
        setSettings,
    };
});
