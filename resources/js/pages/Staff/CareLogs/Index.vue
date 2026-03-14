<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import LightboxModal from '@/Components/Kennel/LightboxModal.vue';
import { ref } from 'vue';
import type { ActivityType, CareLog, CareLogMedia, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    logs: Paginated<CareLog>;
    filters: { activity_type?: string; date_from?: string; date_to?: string };
}>();

const ACTIVITY_TYPES: Array<{ value: ActivityType | ''; label: string }> = [
    { value: '',             label: 'All' },
    { value: 'feeding',      label: 'Feeding' },
    { value: 'walking',      label: 'Walking' },
    { value: 'medication',   label: 'Medication' },
    { value: 'grooming',     label: 'Grooming' },
    { value: 'play',         label: 'Play' },
    { value: 'toilet',       label: 'Toilet' },
    { value: 'health_check', label: 'Health Check' },
    { value: 'other',        label: 'Other' },
];

const activeType = ref<ActivityType | ''>((props.filters.activity_type as ActivityType) ?? '');
const dateFrom   = ref(props.filters.date_from ?? '');
const dateTo     = ref(props.filters.date_to ?? '');

function setType(value: ActivityType | '') {
    activeType.value = value;
    applyFilters();
}

function applyFilters() {
    router.get(
        tenantRoute('staff.care-logs.index'),
        {
            activity_type: activeType.value || undefined,
            date_from:     dateFrom.value || undefined,
            date_to:       dateTo.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function clearFilters() {
    activeType.value = '';
    dateFrom.value   = '';
    dateTo.value     = '';
    applyFilters();
}

// Delete
const deleteModal = ref<InstanceType<typeof ConfirmModal>>();
const logToDelete = ref<CareLog | null>(null);

function confirmDelete(log: CareLog) {
    logToDelete.value = log;
    deleteModal.value?.show();
}

function deleteLog() {
    if (!logToDelete.value) return;
    router.delete(tenantRoute('staff.care-logs.destroy', logToDelete.value.id), { preserveScroll: true });
}

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

const ACTIVITY_COLORS: Record<string, string> = {
    feeding:      'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    walking:      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    medication:   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    grooming:     'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300',
    play:         'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
    toilet:       'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
    health_check: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    other:        'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
};

// Lightbox
const lightboxImages = ref<CareLogMedia[]>([]);
const lightboxIndex = ref(0);
const lightboxOpen = ref(false);

function openLightbox(media: CareLogMedia[], index: number) {
    lightboxImages.value = media;
    lightboxIndex.value = index;
    lightboxOpen.value = true;
}

// Upload media
const uploadLogId = ref<number | null>(null);
const uploadForm = useForm<{ images: File[] }>({ images: [] });
const uploadPreviews = ref<string[]>([]);

function openUpload(logId: number) {
    uploadLogId.value = logId;
    uploadForm.reset();
    uploadPreviews.value = [];
}

function onFilesSelected(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;
    const files = Array.from(input.files).slice(0, 5);
    uploadForm.images = files;
    uploadPreviews.value = files.map((f) => URL.createObjectURL(f));
}

function removeUploadFile(index: number) {
    uploadForm.images.splice(index, 1);
    URL.revokeObjectURL(uploadPreviews.value[index]);
    uploadPreviews.value.splice(index, 1);
}

function submitUpload() {
    if (!uploadLogId.value || uploadForm.images.length === 0) return;
    uploadForm.post(tenantRoute('staff.care-logs.media.store', uploadLogId.value), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            uploadLogId.value = null;
            uploadForm.reset();
            uploadPreviews.value = [];
        },
    });
}

function cancelUpload() {
    uploadLogId.value = null;
    uploadForm.reset();
    uploadPreviews.value.forEach((u) => URL.revokeObjectURL(u));
    uploadPreviews.value = [];
}
</script>

<template>
    <Head title="Care Logs" />

    <PageHeader title="Care Activity Log" :subtitle="`${logs.total} entr${logs.total === 1 ? 'y' : 'ies'}`" />

    <!-- Activity type filter tabs -->
    <div class="mb-4 flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
        <button
            v-for="t in ACTIVITY_TYPES"
            :key="t.value"
            type="button"
            :class="[
                'px-3 py-2 text-sm font-medium transition-colors border-b-2 -mb-px',
                activeType === t.value
                    ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                    : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300',
            ]"
            @click="setType(t.value)"
        >
            {{ t.label }}
        </button>
    </div>

    <!-- Date range + clear -->
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">From</label>
            <input
                v-model="dateFrom"
                type="date"
                class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @change="applyFilters"
            />
        </div>
        <div>
            <label class="block text-xs font-medium text-zinc-500 mb-1">To</label>
            <input
                v-model="dateTo"
                type="date"
                class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @change="applyFilters"
            />
        </div>
        <button
            v-if="dateFrom || dateTo || activeType"
            type="button"
            class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors"
            @click="clearFilters"
        >
            Clear filters
        </button>
    </div>

    <!-- Table -->
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <table v-if="logs.data.length > 0" class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Activity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Dog / Booking</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Notes</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Photos</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Occurred</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500">Logged by</th>
                    <th class="relative px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                    v-for="log in logs.data"
                    :key="log.id"
                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors"
                >
                    <td class="px-4 py-3">
                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                ACTIVITY_COLORS[log.activity_type] ?? ACTIVITY_COLORS.other,
                            ]"
                        >
                            {{ log.activity_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <Link :href="tenantRoute('staff.bookings.show', log.booking_id)" class="group">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 group-hover:text-indigo-600">
                                {{ log.booking?.dog?.name ?? '—' }}
                            </p>
                            <p class="text-xs text-zinc-500">Booking #{{ log.booking_id }}</p>
                        </Link>
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <p v-if="log.notes" class="text-sm text-zinc-600 dark:text-zinc-400 truncate" :title="log.notes">{{ log.notes }}</p>
                        <span v-else class="text-sm text-zinc-300 dark:text-zinc-600">—</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <template v-if="log.media && log.media.length > 0">
                                <button
                                    v-for="(m, idx) in log.media.slice(0, 3)"
                                    :key="m.id"
                                    type="button"
                                    class="h-8 w-8 rounded overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:ring-2 hover:ring-indigo-500 transition-all"
                                    @click="openLightbox(log.media!, idx)"
                                >
                                    <img :src="m.signed_url" class="h-full w-full object-cover" alt="Care log photo" />
                                </button>
                                <span v-if="log.media.length > 3" class="text-xs text-zinc-400">+{{ log.media.length - 3 }}</span>
                            </template>
                            <button
                                type="button"
                                class="h-8 w-8 rounded border border-dashed border-zinc-300 dark:border-zinc-600 flex items-center justify-center text-zinc-400 hover:text-indigo-500 hover:border-indigo-500 transition-colors"
                                title="Upload photos"
                                @click="openUpload(log.id)"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Inline upload form -->
                        <div v-if="uploadLogId === log.id" class="mt-2 p-3 rounded-md border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 space-y-2">
                            <input
                                type="file"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                multiple
                                class="block w-full text-xs text-zinc-500 file:mr-2 file:rounded file:border-0 file:bg-indigo-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300"
                                @change="onFilesSelected"
                            />
                            <div v-if="uploadPreviews.length > 0" class="flex gap-2 flex-wrap">
                                <div v-for="(preview, idx) in uploadPreviews" :key="idx" class="relative h-14 w-14">
                                    <img :src="preview" class="h-full w-full rounded object-cover" />
                                    <button
                                        type="button"
                                        class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-white flex items-center justify-center text-[10px]"
                                        @click="removeUploadFile(idx)"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>
                            <p v-if="uploadForm.errors.images" class="text-xs text-red-600">{{ uploadForm.errors.images }}</p>
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="rounded bg-indigo-600 px-2 py-1 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                                    :disabled="uploadForm.processing || uploadForm.images.length === 0"
                                    @click="submitUpload"
                                >
                                    Upload
                                </button>
                                <button type="button" class="text-xs text-zinc-400 hover:text-zinc-600" @click="cancelUpload">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 whitespace-nowrap">
                        {{ formatDateTime(log.occurred_at) }}
                    </td>
                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ log.logged_by_user?.name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <ConfirmModal
                            ref="deleteModal"
                            title="Delete Log Entry"
                            :description="`Remove this ${log.activity_label} entry? This cannot be undone.`"
                            confirm-label="Delete"
                            :danger="true"
                            @confirm="deleteLog"
                        >
                            <button
                                type="button"
                                class="text-xs text-zinc-400 hover:text-red-600 transition-colors"
                                @click.stop="confirmDelete(log)"
                            >
                                Delete
                            </button>
                        </ConfirmModal>
                    </td>
                </tr>
            </tbody>
        </table>

        <EmptyState
            v-else
            title="No log entries found"
            description="Care activity will appear here once staff start logging during active bookings."
        />

        <Pagination v-if="logs.last_page > 1" :paginator="logs" />
    </div>

    <!-- Lightbox -->
    <LightboxModal
        v-if="lightboxOpen"
        :images="lightboxImages"
        :initial-index="lightboxIndex"
        @close="lightboxOpen = false"
    />
</template>
