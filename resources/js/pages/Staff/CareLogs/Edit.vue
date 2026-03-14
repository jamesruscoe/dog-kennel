<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import StatusBadge from '@/Components/Kennel/StatusBadge.vue';
import type { CareLog, CareLogMedia } from '@/types/kennel';
import { useTenantRoute } from '@/composables/useTenantRoute';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{ careLog: CareLog }>();

const ACTIVITY_TYPES = [
    { value: 'feeding',      label: 'Feeding' },
    { value: 'walking',      label: 'Walking' },
    { value: 'medication',   label: 'Medication' },
    { value: 'grooming',     label: 'Grooming' },
    { value: 'play',         label: 'Play' },
    { value: 'toilet',       label: 'Toilet' },
    { value: 'health_check', label: 'Health Check' },
    { value: 'other',        label: 'Other' },
];

const ACTIVITY_COLORS: Record<string, string> = {
    feeding:      'bg-orange-100 text-orange-700',
    walking:      'bg-blue-100 text-blue-700',
    medication:   'bg-red-100 text-red-700',
    grooming:     'bg-pink-100 text-pink-700',
    play:         'bg-green-100 text-green-700',
    toilet:       'bg-yellow-100 text-yellow-700',
    health_check: 'bg-purple-100 text-purple-700',
    other:        'bg-zinc-100 text-zinc-600',
};

// Convert ISO occurred_at to datetime-local format
function toLocalDatetime(iso: string): string {
    const d = new Date(iso);
    d.setSeconds(0, 0);
    const offset = d.getTimezoneOffset();
    const local = new Date(d.getTime() - offset * 60000);
    return local.toISOString().slice(0, 16);
}

// Track existing photos that user wants to keep / remove
const existingMedia = ref<CareLogMedia[]>(
    (props.careLog.media ?? []).filter(m => m.signed_url)
);
const deletedMediaIds = ref<number[]>([]);

function removeExisting(media: CareLogMedia) {
    deletedMediaIds.value.push(media.id);
    existingMedia.value = existingMedia.value.filter(m => m.id !== media.id);
}

const remainingSlots = computed(() => 5 - existingMedia.value.length - newImages.value.length);

// New images
const newImages = ref<File[]>([]);
const newPreviews = ref<string[]>([]);

function onFilesSelected(e: Event) {
    const input = e.target as HTMLInputElement;
    if (!input.files) return;
    const files = Array.from(input.files).slice(0, remainingSlots.value);
    newImages.value.push(...files);
    newPreviews.value.push(...files.map((f) => URL.createObjectURL(f)));
    input.value = '';
}

function removeNewImage(index: number) {
    newImages.value.splice(index, 1);
    URL.revokeObjectURL(newPreviews.value[index]);
    newPreviews.value.splice(index, 1);
}

const form = useForm<{
    _method: string;
    activity_type: string;
    notes: string;
    occurred_at: string;
    images: File[];
    delete_media_ids: number[];
}>({
    _method: 'PATCH',
    activity_type: props.careLog.activity_type,
    notes: props.careLog.notes ?? '',
    occurred_at: toLocalDatetime(props.careLog.occurred_at),
    images: [],
    delete_media_ids: [],
});

function submit() {
    form.images = newImages.value;
    form.delete_media_ids = deletedMediaIds.value;
    form.post(tenantRoute('staff.care-logs.update', props.careLog.id), {
        forceFormData: true,
    });
}

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>

<template>
    <Head :title="`Edit Care Log #${careLog.id}`" />

    <PageHeader
        title="Edit Care Log"
        :breadcrumbs="[
            { label: 'Care Logs', href: tenantRoute('staff.care-logs.index') },
            { label: `#${careLog.id}`, href: tenantRoute('staff.care-logs.show', careLog.id) },
            { label: 'Edit' },
        ]"
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 max-w-4xl">
        <!-- Form -->
        <div class="lg:col-span-2">
            <form class="space-y-5" @submit.prevent="submit">
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 space-y-5">

                    <!-- Activity type -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Activity Type <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                v-for="t in ACTIVITY_TYPES"
                                :key="t.value"
                                type="button"
                                :class="[
                                    'rounded-lg border px-2 py-2.5 text-xs font-medium text-center transition-colors',
                                    form.activity_type === t.value
                                        ? `${ACTIVITY_COLORS[t.value]} border-transparent ring-2 ring-offset-1 ring-indigo-500`
                                        : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-indigo-300',
                                ]"
                                @click="form.activity_type = t.value"
                            >
                                {{ t.label }}
                            </button>
                        </div>
                        <p v-if="form.errors.activity_type" class="mt-1 text-xs text-red-600">{{ form.errors.activity_type }}</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Notes</label>
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Describe what happened — amount eaten, distance walked, any observations…"
                            class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
                    </div>

                    <!-- Occurred at -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            When did this occur?
                        </label>
                        <input
                            v-model="form.occurred_at"
                            type="datetime-local"
                            class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.occurred_at" class="mt-1 text-xs text-red-600">{{ form.errors.occurred_at }}</p>
                    </div>

                    <!-- Photos -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Photos <span class="text-xs font-normal text-zinc-400">(max 5 total)</span>
                        </label>

                        <!-- Existing photos -->
                        <div v-if="existingMedia.length > 0" class="flex gap-2 flex-wrap mb-2">
                            <div v-for="m in existingMedia" :key="m.id" class="relative h-20 w-20">
                                <img :src="m.signed_url!" class="h-full w-full rounded-lg object-cover border border-zinc-200 dark:border-zinc-700" />
                                <button
                                    type="button"
                                    class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                    title="Remove photo"
                                    @click="removeExisting(m)"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>

                        <!-- New photo previews -->
                        <div v-if="newPreviews.length > 0" class="flex gap-2 flex-wrap mb-2">
                            <div v-for="(preview, idx) in newPreviews" :key="`new-${idx}`" class="relative h-20 w-20">
                                <img :src="preview" class="h-full w-full rounded-lg object-cover border-2 border-dashed border-indigo-300 dark:border-indigo-700" />
                                <button
                                    type="button"
                                    class="absolute -top-1.5 -right-1.5 h-5 w-5 rounded-full bg-red-500 text-white flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                    @click="removeNewImage(idx)"
                                >
                                    &times;
                                </button>
                            </div>
                        </div>

                        <input
                            v-if="remainingSlots > 0"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            multiple
                            class="block w-full text-sm text-zinc-500 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 file:cursor-pointer"
                            @change="onFilesSelected"
                        />
                        <p v-if="form.errors.images" class="mt-1 text-xs text-red-600">{{ form.errors.images }}</p>
                        <p v-if="form.errors.delete_media_ids" class="mt-1 text-xs text-red-600">{{ form.errors.delete_media_ids }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="tenantRoute('staff.care-logs.show', careLog.id)"
                        class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Booking context sidebar -->
        <div v-if="careLog.booking" class="lg:col-span-1">
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 space-y-3 sticky top-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Booking #{{ careLog.booking_id }}</h3>
                    <StatusBadge :status="careLog.booking.status" />
                </div>
                <dl class="space-y-2 text-sm">
                    <div v-if="careLog.booking.dog">
                        <dt class="text-xs font-medium text-zinc-500 uppercase tracking-wide">Dog</dt>
                        <dd class="text-zinc-800 dark:text-zinc-200">{{ careLog.booking.dog.name }}</dd>
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
    </div>
</template>
