<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import EmptyState from '@/Components/Kennel/EmptyState.vue';
import Pagination from '@/Components/Kennel/Pagination.vue';
import LightboxModal from '@/Components/Kennel/LightboxModal.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import type { ActivityType, CareLog, CareLogMedia, Paginated } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    logs: Paginated<CareLog>;
    filters: { type?: string };
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

const activeTab = ref<'feed' | 'gallery'>('feed');
const activeType = ref<ActivityType | ''>((props.filters.type as ActivityType) ?? '');

function setType(value: ActivityType | '') {
    activeType.value = value;
    router.get(
        tenantRoute('owner.updates.index'),
        { type: value || undefined },
        { preserveState: true, replace: true, preserveScroll: true },
    );
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

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

// Lightbox
const lightboxImages = ref<CareLogMedia[]>([]);
const lightboxIndex = ref(0);
const lightboxOpen = ref(false);

function openLightbox(media: CareLogMedia[], index: number) {
    lightboxImages.value = media;
    lightboxIndex.value = index;
    lightboxOpen.value = true;
}

// Collect all media grouped by date for gallery view
interface GalleryGroup {
    date: string;
    label: string;
    media: CareLogMedia[];
}

function getGalleryGroups(): GalleryGroup[] {
    const groups: Map<string, CareLogMedia[]> = new Map();
    for (const log of props.logs.data) {
        const validMedia = log.media?.filter(m => m.signed_url) ?? [];
        if (!validMedia.length) continue;
        const dateKey = log.occurred_at.split(' ')[0] ?? log.occurred_at.split('T')[0];
        const existing = groups.get(dateKey) ?? [];
        existing.push(...validMedia);
        groups.set(dateKey, existing);
    }
    return Array.from(groups.entries()).map(([date, media]) => ({
        date,
        label: formatDate(date),
        media,
    }));
}

function openGalleryLightbox(groups: GalleryGroup[], groupIdx: number, mediaIdx: number) {
    const allMedia = groups.flatMap((g) => g.media);
    let offset = 0;
    for (let i = 0; i < groupIdx; i++) {
        offset += groups[i].media.length;
    }
    lightboxImages.value = allMedia;
    lightboxIndex.value = offset + mediaIdx;
    lightboxOpen.value = true;
}
</script>

<template>
    <Head title="Updates" />

    <PageHeader title="Updates" subtitle="Care activity for your dogs" />

    <!-- Feed / Gallery toggle -->
    <div class="mb-4 flex gap-2">
        <button
            type="button"
            :class="[
                'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                activeTab === 'feed'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700',
            ]"
            @click="activeTab = 'feed'"
        >
            Feed
        </button>
        <button
            type="button"
            :class="[
                'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                activeTab === 'gallery'
                    ? 'bg-indigo-600 text-white'
                    : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700',
            ]"
            @click="activeTab = 'gallery'"
        >
            Gallery
        </button>
    </div>

    <!-- Activity type filter tabs (feed view) -->
    <div v-if="activeTab === 'feed'" class="mb-4 flex flex-wrap gap-1 border-b border-zinc-200 dark:border-zinc-700">
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

    <!-- Feed view -->
    <div v-if="activeTab === 'feed'">
        <div v-if="logs.data.length > 0" class="space-y-4">
            <div
                v-for="log in logs.data"
                :key="log.id"
                class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5"
            >
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span
                            :class="[
                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                ACTIVITY_COLORS[log.activity_type] ?? ACTIVITY_COLORS.other,
                            ]"
                        >
                            {{ log.activity_label }}
                        </span>
                        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                            {{ log.booking?.dog?.name ?? '—' }}
                        </span>
                    </div>
                    <span class="text-xs text-zinc-400">
                        {{ formatDateTime(log.occurred_at) }}
                    </span>
                </div>

                <p v-if="log.notes" class="text-sm text-zinc-600 dark:text-zinc-400 mb-3">{{ log.notes }}</p>

                <!-- Image grid -->
                <div v-if="log.media && log.media.filter(m => m.signed_url).length > 0" class="flex flex-wrap gap-2 mb-3">
                    <button
                        v-for="(m, idx) in log.media.filter(m => m.signed_url)"
                        :key="m.id"
                        type="button"
                        class="h-20 w-20 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:ring-2 hover:ring-indigo-500 transition-all"
                        @click="openLightbox(log.media!.filter(m => m.signed_url), idx)"
                    >
                        <img :src="m.signed_url" class="h-full w-full object-cover" alt="Care log photo" />
                    </button>
                </div>

                <Link
                    :href="tenantRoute('owner.updates.show', log.id)"
                    class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
                >
                    View details &rarr;
                </Link>
            </div>
        </div>

        <div v-else class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <EmptyState
                title="No updates yet"
                description="Care activity for your dogs will appear here."
            />
        </div>

        <Pagination v-if="logs.last_page > 1" :paginator="logs" class="mt-4" />
    </div>

    <!-- Gallery view -->
    <div v-if="activeTab === 'gallery'">
        <template v-if="getGalleryGroups().length > 0">
            <div v-for="(group, gIdx) in getGalleryGroups()" :key="group.date" class="mb-6">
                <h3 class="text-sm font-medium text-zinc-500 mb-2">{{ group.label }}</h3>
                <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                    <button
                        v-for="(m, mIdx) in group.media"
                        :key="m.id"
                        type="button"
                        class="aspect-square rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:ring-2 hover:ring-indigo-500 transition-all"
                        @click="openGalleryLightbox(getGalleryGroups(), gIdx, mIdx)"
                    >
                        <img :src="m.signed_url" class="h-full w-full object-cover" alt="Photo" />
                    </button>
                </div>
            </div>
        </template>
        <div v-else class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
            <EmptyState
                title="No photos yet"
                description="Photos from care activities will appear here."
            />
        </div>
    </div>

    <!-- Lightbox -->
    <LightboxModal
        v-if="lightboxOpen"
        :images="lightboxImages"
        :initial-index="lightboxIndex"
        @close="lightboxOpen = false"
    />
</template>
