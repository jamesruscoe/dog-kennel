<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import type { KennelSettings } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{ settings: KennelSettings }>();

// ─── Month state ─────────────────────────────────────────────────────────────

const today = new Date();
const year  = ref(today.getFullYear());
const month = ref(today.getMonth() + 1); // 1–12

const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const monthLabel = computed(() => `${MONTH_NAMES[month.value - 1]} ${year.value}`);

function prevMonth() {
    if (month.value === 1) { year.value--; month.value = 12; }
    else { month.value--; }
}

function nextMonth() {
    if (month.value === 12) { year.value++; month.value = 1; }
    else { month.value++; }
}

function goToday() {
    year.value  = today.getFullYear();
    month.value = today.getMonth() + 1;
}

// ─── Occupancy fetch ─────────────────────────────────────────────────────────

const occupancyMap = ref<Record<string, number>>({});
const maxCapacity  = ref(props.settings.max_capacity);
const loading      = ref(false);

async function fetchOccupancy() {
    loading.value = true;
    try {
        const url = route('staff.calendar.occupancy', { year: year.value, month: month.value });
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        occupancyMap.value = data.occupancy ?? {};
        maxCapacity.value  = data.max_capacity ?? props.settings.max_capacity;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchOccupancy);
watch([year, month], fetchOccupancy);

// ─── Calendar grid ────────────────────────────────────────────────────────────

interface CalDay {
    date: string;
    day: number;
    isoWeekday: number; // 1=Mon … 7=Sun
    isToday: boolean;
    isPast: boolean;
    isOperating: boolean;
    occupancy: number;
    pct: number;
    isFull: boolean;
}

const todayStr = today.toISOString().slice(0, 10);

// Returns JS Date -> ISO weekday 1=Mon … 7=Sun
function isoWeekday(d: Date): number {
    return ((d.getDay() + 6) % 7) + 1;
}

// dateStr from local date parts (avoids UTC-offset bug)
function localDateStr(y: number, m: number, d: number): string {
    return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
}

const calendarWeeks = computed<(CalDay | null)[][]>(() => {
    const firstDay     = new Date(year.value, month.value - 1, 1);
    const startOffset  = isoWeekday(firstDay) - 1; // leading empty cells
    const daysInMonth  = new Date(year.value, month.value, 0).getDate();

    const cells: (CalDay | null)[] = Array(startOffset).fill(null);

    for (let d = 1; d <= daysInMonth; d++) {
        const date    = new Date(year.value, month.value - 1, d);
        const dateStr = localDateStr(year.value, month.value, d);
        const occ     = occupancyMap.value[dateStr] ?? 0;
        const pct     = maxCapacity.value > 0 ? (occ / maxCapacity.value) * 100 : 0;
        const iso     = isoWeekday(date);

        cells.push({
            date:        dateStr,
            day:         d,
            isoWeekday:  iso,
            isToday:     dateStr === todayStr,
            isPast:      dateStr < todayStr,
            isOperating: props.settings.operating_days.includes(iso),
            occupancy:   occ,
            pct,
            isFull:      occ >= maxCapacity.value,
        });
    }

    while (cells.length % 7 !== 0) cells.push(null);

    const weeks: (CalDay | null)[][] = [];
    for (let i = 0; i < cells.length; i += 7) weeks.push(cells.slice(i, i + 7));
    return weeks;
});

// ─── Selected day ─────────────────────────────────────────────────────────────

const selectedDate = ref<string | null>(null);

const selectedDay = computed<CalDay | null>(() => {
    if (!selectedDate.value) return null;
    return calendarWeeks.value.flat().find(d => d?.date === selectedDate.value) ?? null;
});

function selectDay(day: CalDay | null) {
    if (!day) return;
    selectedDate.value = selectedDate.value === day.date ? null : day.date;
}

// ─── Styling helpers ─────────────────────────────────────────────────────────

function cellClass(day: CalDay | null): string {
    if (!day) return 'bg-zinc-50 dark:bg-zinc-800/30';
    if (!day.isOperating) return 'bg-zinc-100 dark:bg-zinc-800/60 opacity-60 cursor-default';
    if (day.isToday)      return 'ring-2 ring-inset ring-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 cursor-pointer';
    if (selectedDate.value === day.date) return 'ring-2 ring-inset ring-zinc-400 dark:ring-zinc-500 bg-white dark:bg-zinc-900 cursor-pointer';
    return 'bg-white dark:bg-zinc-900 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer';
}

function barColor(day: CalDay): string {
    if (day.isFull)      return 'bg-red-500';
    if (day.pct >= 75)   return 'bg-amber-400';
    if (day.occupancy > 0) return 'bg-green-400';
    return 'bg-zinc-200 dark:bg-zinc-700';
}

function occupancyTextColor(day: CalDay): string {
    if (day.isFull)      return 'text-red-600 dark:text-red-400';
    if (day.pct >= 75)   return 'text-amber-600 dark:text-amber-400';
    if (day.occupancy > 0) return 'text-green-700 dark:text-green-400';
    return 'text-zinc-400 dark:text-zinc-500';
}

function formatSelectedDate(dateStr: string): string {
    return new Date(dateStr + 'T12:00:00').toLocaleDateString('en-GB', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
    });
}
</script>

<template>
    <Head title="Calendar" />

    <PageHeader title="Availability Calendar" subtitle="Occupancy by date — click a day for details." />

    <div class="flex gap-6">
        <!-- Calendar panel -->
        <div class="flex-1 min-w-0">
            <!-- Month nav -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                        @click="prevMonth"
                    >
                        ‹
                    </button>
                    <h2 class="w-44 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ monthLabel }}
                    </h2>
                    <button
                        type="button"
                        class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                        @click="nextMonth"
                    >
                        ›
                    </button>
                </div>
                <button
                    type="button"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-xs font-medium text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                    @click="goToday"
                >
                    Today
                </button>
            </div>

            <!-- Calendar grid -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                <!-- Day headers -->
                <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                    <div
                        v-for="label in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']"
                        :key="label"
                        class="py-2 text-center text-xs font-medium text-zinc-500 uppercase tracking-wide"
                    >
                        {{ label }}
                    </div>
                </div>

                <!-- Weeks -->
                <div
                    v-for="(week, wi) in calendarWeeks"
                    :key="wi"
                    class="grid grid-cols-7"
                    :class="wi < calendarWeeks.length - 1 ? 'border-b border-zinc-100 dark:border-zinc-800' : ''"
                >
                    <div
                        v-for="(day, di) in week"
                        :key="di"
                        :class="[
                            'relative min-h-[72px] p-2 flex flex-col justify-between transition-colors',
                            di < 6 ? 'border-r border-zinc-100 dark:border-zinc-800' : '',
                            cellClass(day),
                        ]"
                        @click="selectDay(day)"
                    >
                        <template v-if="day">
                            <!-- Day number + closed badge -->
                            <div class="flex items-start justify-between">
                                <span
                                    :class="[
                                        'text-xs font-semibold leading-none',
                                        day.isToday
                                            ? 'flex h-5 w-5 items-center justify-center rounded-full bg-indigo-600 text-white text-[10px]'
                                            : day.isPast
                                                ? 'text-zinc-400 dark:text-zinc-600'
                                                : 'text-zinc-700 dark:text-zinc-200',
                                    ]"
                                >
                                    {{ day.day }}
                                </span>
                                <span
                                    v-if="!day.isOperating"
                                    class="text-[9px] font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500 leading-none mt-0.5"
                                >
                                    Closed
                                </span>
                            </div>

                            <!-- Occupancy count + bar (operating days only) -->
                            <div v-if="day.isOperating" class="mt-1">
                                <div class="flex items-end justify-between mb-1">
                                    <span
                                        v-if="loading"
                                        class="text-[10px] text-zinc-300 dark:text-zinc-600"
                                    >…</span>
                                    <span
                                        v-else
                                        :class="['text-[11px] font-medium leading-none', occupancyTextColor(day)]"
                                    >
                                        {{ day.occupancy }}/{{ maxCapacity }}
                                    </span>
                                    <span
                                        v-if="day.isFull"
                                        class="text-[9px] font-semibold uppercase tracking-wide text-red-500 leading-none"
                                    >
                                        Full
                                    </span>
                                </div>
                                <!-- Bar -->
                                <div class="h-1 w-full rounded-full bg-zinc-100 dark:bg-zinc-700 overflow-hidden">
                                    <div
                                        :class="['h-full rounded-full transition-all', barColor(day)]"
                                        :style="{ width: `${Math.min(100, day.pct)}%` }"
                                    />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="mt-3 flex items-center gap-5 text-xs text-zinc-500">
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-green-400" />
                    Available
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-400" />
                    &gt;75% full
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-red-500" />
                    Full
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600" />
                    Closed
                </span>
            </div>
        </div>

        <!-- Detail panel -->
        <div class="w-64 shrink-0">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden sticky top-6">
                <template v-if="selectedDay">
                    <div class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800">
                        <p class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ formatSelectedDate(selectedDay.date) }}
                        </p>
                    </div>
                    <div class="px-4 py-4 space-y-4">
                        <!-- Closed -->
                        <div v-if="!selectedDay.isOperating" class="text-sm text-zinc-500">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 px-2.5 py-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                                Closed
                            </span>
                            <p class="mt-2 text-xs">This day is not in the operating schedule.</p>
                        </div>

                        <!-- Occupancy -->
                        <template v-else>
                            <div>
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wide mb-2">Occupancy</p>
                                <div class="flex items-end gap-2 mb-2">
                                    <span class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 leading-none">
                                        {{ selectedDay.occupancy }}
                                    </span>
                                    <span class="text-sm text-zinc-400 leading-none mb-1">/ {{ maxCapacity }}</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700 overflow-hidden">
                                    <div
                                        :class="['h-full rounded-full', barColor(selectedDay)]"
                                        :style="{ width: `${Math.min(100, selectedDay.pct)}%` }"
                                    />
                                </div>
                                <p :class="['mt-1.5 text-xs font-medium', occupancyTextColor(selectedDay)]">
                                    {{ selectedDay.isFull ? 'Full — no capacity' : `${maxCapacity - selectedDay.occupancy} space${maxCapacity - selectedDay.occupancy === 1 ? '' : 's'} available` }}
                                </p>
                            </div>

                            <!-- Quick link to bookings for that date -->
                            <Link
                                :href="route('staff.bookings.index', { date_from: selectedDay.date, date_to: selectedDay.date })"
                                class="block w-full rounded-md border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-center text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:border-indigo-400 hover:text-indigo-600 transition-colors"
                            >
                                View bookings for this day
                            </Link>
                        </template>
                    </div>
                </template>

                <div v-else class="px-4 py-8 text-center">
                    <p class="text-sm text-zinc-400">Select a day to see occupancy details.</p>
                </div>
            </div>
        </div>
    </div>
</template>
