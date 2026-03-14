<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps<{
    images: Array<{ signed_url: string }>;
    initialIndex?: number;
}>();

const emit = defineEmits<{ close: [] }>();

const currentIndex = ref(props.initialIndex ?? 0);

watch(() => props.initialIndex, (val) => {
    currentIndex.value = val ?? 0;
});

function prev() {
    currentIndex.value = currentIndex.value > 0
        ? currentIndex.value - 1
        : props.images.length - 1;
}

function next() {
    currentIndex.value = currentIndex.value < props.images.length - 1
        ? currentIndex.value + 1
        : 0;
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') emit('close');
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
}
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80"
            @click.self="emit('close')"
            @keydown="onKeydown"
            tabindex="0"
            ref="backdrop"
        >
            <!-- Close button -->
            <button
                type="button"
                class="absolute top-4 right-4 rounded-full bg-black/50 p-2 text-white hover:bg-black/70 transition-colors z-10"
                @click="emit('close')"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Previous button -->
            <button
                v-if="images.length > 1"
                type="button"
                class="absolute left-4 rounded-full bg-black/50 p-2 text-white hover:bg-black/70 transition-colors z-10"
                @click="prev"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Image -->
            <img
                :src="images[currentIndex]?.signed_url"
                class="max-h-[90vh] max-w-[90vw] object-contain rounded-lg"
                alt="Care log photo"
            />

            <!-- Next button -->
            <button
                v-if="images.length > 1"
                type="button"
                class="absolute right-4 rounded-full bg-black/50 p-2 text-white hover:bg-black/70 transition-colors z-10"
                @click="next"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Counter -->
            <div v-if="images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>
        </div>
    </Teleport>
</template>
