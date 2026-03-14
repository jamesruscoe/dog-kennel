<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import { useTenantRoute } from '@/composables/useTenantRoute';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import ConfirmModal from '@/Components/Kennel/ConfirmModal.vue';
import LightboxModal from '@/Components/Kennel/LightboxModal.vue';
import { computed, ref } from 'vue';
import type { CareLog, CareLogMedia } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();
const page = usePage();

const props = defineProps<{ careLog: CareLog }>();

const user = computed(() => page.props.auth.user);
const canEdit = computed(() => ['staff', 'admin'].includes(user.value?.role ?? ''));

const breadcrumbs = computed(() => {
    if (canEdit.value) {
        return [
            { label: 'Care Logs', href: tenantRoute('staff.care-logs.index') },
            { label: `#${props.careLog.id}` },
        ];
    }
    return [
        { label: 'Updates', href: tenantRoute('owner.updates.index') },
        { label: props.careLog.activity_label },
    ];
});

// Delete
const deleteModal = ref<InstanceType<typeof ConfirmModal>>();

function deleteCareLog() {
    router.delete(tenantRoute('staff.care-logs.destroy', props.careLog.id), {
        onSuccess: () => router.visit(tenantRoute('staff.care-logs.index')),
    });
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

const validMedia = computed(() =>
    (props.careLog.media ?? []).filter(m => m.signed_url)
);

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

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function formatDateTime(d: string) {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}
</script>

<template>
    <Head :title="careLog.activity_label" />

    <PageHeader :title="careLog.activity_label" :breadcrumbs="breadcrumbs">
        <template v-if="canEdit" #actions>
            <Link
                :href="tenantRoute('staff.care-logs.edit', careLog.id)"
                class="inline-flex items-center rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
            >
                Edit
            </Link>

            <ConfirmModal
                ref="deleteModal"
                title="Delete Care Log"
                description="Remove this care log entry? This cannot be undone."
                confirm-label="Delete"
                :danger="true"
                @confirm="deleteCareLog"
            >
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-red-300 dark:border-red-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    @click.stop="deleteModal?.show()"
                >
                    Delete
                </button>
            </ConfirmModal>
        </template>
    </PageHeader>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 max-w-5xl">
        <!-- Sidebar: Booking context -->
        <div class="lg:col-span-1 order-2 lg:order-1">
            <div v-if="careLog.booking" class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 space-y-3 sticky top-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking #{{ careLog.booking_id }}</h3>
                    <StatusBadge :status="careLog.booking.status" />
                </div>
                <dl class="space-y-2 text-sm">
                    <div v-if="careLog.booking.dog">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Dog</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">
                            <Link
                                v-if="canEdit"
                                :href="tenantRoute('staff.dogs.show', careLog.booking.dog.id)"
                                class="hover:text-indigo-600 transition-colors"
                            >
                                {{ careLog.booking.dog.name }}
                            </Link>
                            <span v-else>{{ careLog.booking.dog.name }}</span>
                        </dd>
                    </div>
                    <div v-if="careLog.booking.dog?.owner">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Owner</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ careLog.booking.dog.owner.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Stay</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">
                            {{ formatDate(careLog.booking.check_in_date) }} – {{ formatDate(careLog.booking.check_out_date) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Main content -->
        <div class="lg:col-span-2 order-1 lg:order-2 space-y-6">
            <!-- Details card -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span
                        :class="[
                            'inline-flex items-center rounded-full px-3 py-1 text-xs font-medium',
                            ACTIVITY_COLORS[careLog.activity_type] ?? ACTIVITY_COLORS.other,
                        ]"
                    >
                        {{ careLog.activity_label }}
                    </span>
                </div>

                <div v-if="careLog.notes" class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ careLog.notes }}</div>
                <p v-else class="text-sm text-zinc-400 italic">No notes recorded.</p>

                <dl class="grid grid-cols-2 gap-4 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Occurred</dt>
                        <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ formatDateTime(careLog.occurred_at) }}</dd>
                    </div>
                    <div v-if="careLog.logged_by_user">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Logged by</dt>
                        <dd class="text-sm text-zinc-800 dark:text-zinc-200">{{ careLog.logged_by_user.name }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Photos card -->
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Photos</h3>

                <div v-if="validMedia.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <button
                        v-for="(m, idx) in validMedia"
                        :key="m.id"
                        type="button"
                        class="aspect-square rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:ring-2 hover:ring-indigo-500 transition-all"
                        @click="openLightbox(validMedia, idx)"
                    >
                        <img :src="m.signed_url!" class="h-full w-full object-cover" alt="Care log photo" />
                    </button>
                </div>
                <p v-else class="text-sm text-zinc-400">No photos attached.</p>
            </div>
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
