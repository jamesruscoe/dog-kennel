<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import type { Booking, StaffMetrics } from '@/types/kennel';

Chart.register(...registerables);

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    metrics: StaffMetrics;
    maxCapacity: number;
    occupancy14: Record<string, number>;
    checkInsToday: Booking[];
    checkOutsToday: Booking[];
    pendingBookings: Booking[];
}>();

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    // Append T00:00:00 to force local-timezone parse
    return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', {
        weekday: 'short', day: 'numeric', month: 'short',
    });
}

function formatChartLabel(dateStr: string) {
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
}

// ─────────────────────────────────────────────────────────────────────────────
// 14-day occupancy chart
// ─────────────────────────────────────────────────────────────────────────────

const chartCanvas = ref<HTMLCanvasElement | null>(null);

onMounted(() => {
    if (! chartCanvas.value) return;

    const dates        = Object.keys(props.occupancy14);
    const occupancies  = Object.values(props.occupancy14);
    const capacityLine = occupancies.map(() => props.maxCapacity);

    new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
            labels: dates.map(formatChartLabel),
            datasets: [
                {
                    label: 'Dogs in Stay',
                    data: occupancies,
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2,
                },
                {
                    label: 'Max Capacity',
                    data: capacityLine,
                    type: 'line',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 2,
                    borderDash: [5, 4],
                    pointRadius: 0,
                    fill: false,
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12 } },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: props.maxCapacity + 2,
                    ticks: { stepSize: 1, precision: 0 },
                    grid: { color: 'rgba(0,0,0,0.06)' },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Stat card helper
// ─────────────────────────────────────────────────────────────────────────────

interface StatCard {
    label: string;
    value: string | number;
    sub?: string;
    accent?: 'amber' | 'emerald' | 'blue' | 'indigo';
}

const stats: StatCard[] = [
    {
        label: 'In Stay Today',
        value: props.metrics.in_stay_today,
        sub: `of ${props.maxCapacity} capacity`,
        accent: 'indigo',
    },
    {
        label: 'Arriving Today',
        value: props.metrics.todays_checkins,
        accent: 'emerald',
    },
    {
        label: 'Departing Today',
        value: props.metrics.todays_checkouts,
        accent: 'blue',
    },
    {
        label: 'Awaiting Approval',
        value: props.metrics.pending_approval,
        accent: 'amber',
    },
    {
        label: 'Active Bookings',
        value: props.metrics.active_bookings,
        sub: 'pending + approved',
    },
    {
        label: 'Revenue This Month',
        value: props.metrics.revenue_display,
        sub: 'confirmed payments',
        accent: 'emerald',
    },
];

const accentBorder: Record<string, string> = {
    amber:   'border-l-amber-400',
    emerald: 'border-l-emerald-400',
    blue:    'border-l-blue-400',
    indigo:  'border-l-indigo-400',
};
const accentText: Record<string, string> = {
    amber:   'text-amber-600 dark:text-amber-400',
    emerald: 'text-emerald-600 dark:text-emerald-400',
    blue:    'text-blue-600 dark:text-blue-400',
    indigo:  'text-indigo-600 dark:text-indigo-400',
};
</script>

<template>
    <Head title="Staff Dashboard" />

    <PageHeader title="Dashboard" subtitle="Kennel overview" />

    <!-- ── 6 KPI cards ──────────────────────────────────────────────────────── -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div
            v-for="stat in stats"
            :key="stat.label"
            :class="[
                'rounded-lg border bg-white dark:bg-zinc-900 p-5 border-l-4',
                stat.accent ? accentBorder[stat.accent] : 'border-l-zinc-300 dark:border-l-zinc-600',
                'border-t border-r border-b border-zinc-200 dark:border-zinc-700',
            ]"
        >
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">{{ stat.label }}</p>
            <p
                :class="[
                    'mt-1 text-3xl font-bold',
                    stat.accent ? accentText[stat.accent] : 'text-zinc-900 dark:text-zinc-100',
                ]"
            >
                {{ stat.value }}
            </p>
            <p v-if="stat.sub" class="mt-0.5 text-xs text-zinc-400">{{ stat.sub }}</p>
        </div>
    </div>

    <!-- ── Main grid: chart + today's movements ─────────────────────────────── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- 14-day occupancy chart -->
        <div class="lg:col-span-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                14-Day Occupancy Forecast
            </h2>
            <div class="relative h-64">
                <canvas ref="chartCanvas" />
            </div>
        </div>

        <!-- Today's movements -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Today's Movements</h2>
            </div>

            <!-- Arriving -->
            <div class="px-5 py-3 border-b border-zinc-100 dark:border-zinc-800">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 mb-2">
                    Arriving ({{ checkInsToday.length }})
                </p>
                <ul v-if="checkInsToday.length" class="space-y-2">
                    <li v-for="b in checkInsToday" :key="b.id" class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <Link
                                :href="route('staff.bookings.show', b.id)"
                                class="text-sm font-medium text-zinc-800 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 truncate block"
                            >
                                {{ b.dog?.name ?? '—' }}
                            </Link>
                            <p class="text-xs text-zinc-400 truncate">{{ b.dog?.owner?.name ?? '—' }}</p>
                        </div>
                        <StatusBadge :status="b.status" />
                    </li>
                </ul>
                <p v-else class="text-xs text-zinc-400 italic">No check-ins today.</p>
            </div>

            <!-- Departing -->
            <div class="px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400 mb-2">
                    Departing ({{ checkOutsToday.length }})
                </p>
                <ul v-if="checkOutsToday.length" class="space-y-2">
                    <li v-for="b in checkOutsToday" :key="b.id" class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <Link
                                :href="route('staff.bookings.show', b.id)"
                                class="text-sm font-medium text-zinc-800 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 truncate block"
                            >
                                {{ b.dog?.name ?? '—' }}
                            </Link>
                            <p class="text-xs text-zinc-400 truncate">{{ b.dog?.owner?.name ?? '—' }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-zinc-500">{{ formatDate(b.check_out_date) }}</span>
                    </li>
                </ul>
                <p v-else class="text-xs text-zinc-400 italic">No check-outs today.</p>
            </div>
        </div>
    </div>

    <!-- ── Pending bookings ──────────────────────────────────────────────────── -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Pending Approval</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Booking requests awaiting a decision.</p>
            </div>
            <Link
                :href="route('staff.bookings.index', { status: 'pending' })"
                class="text-xs font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
            >
                View all →
            </Link>
        </div>

        <div v-if="pendingBookings.length === 0" class="px-6 py-8 text-center text-sm text-zinc-400">
            No bookings awaiting approval.
        </div>

        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-800 text-left">
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Dog</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Owner</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Check-in</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Check-out</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Nights</th>
                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">Amount</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr v-for="b in pendingBookings" :key="b.id">
                    <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ b.dog?.name ?? '—' }}</td>
                    <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ b.dog?.owner?.name ?? '—' }}</td>
                    <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">{{ formatDate(b.check_in_date) }}</td>
                    <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400 whitespace-nowrap">{{ formatDate(b.check_out_date) }}</td>
                    <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ b.nights }}</td>
                    <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ b.amount_display ?? '—' }}</td>
                    <td class="px-6 py-3 text-right">
                        <Link
                            :href="route('staff.bookings.show', b.id)"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 whitespace-nowrap"
                        >
                            Review →
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
