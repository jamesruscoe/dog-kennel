<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    title: string;
    description: string;
    confirmLabel?: string;
    danger?: boolean;
}>();

const emit = defineEmits<{
    confirm: [];
}>();

const open = ref(false);

function show() { open.value = true; }
function hide() { open.value = false; }
function confirm() {
    emit('confirm');
    hide();
}

defineExpose({ show, hide });
</script>

<template>
    <!-- Trigger slot -->
    <span @click.prevent="show">
        <slot />
    </span>

    <!-- Backdrop + dialog -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50"
                    @click="hide"
                />

                <!-- Dialog -->
                <div class="relative w-full max-w-md rounded-lg bg-white dark:bg-zinc-800 shadow-xl p-6">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-full', danger ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30']">
                            <svg class="h-5 w-5" :class="danger ? 'text-red-600' : 'text-amber-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ title }}</h3>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-600 transition-colors"
                            @click="hide"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            :class="[
                                'rounded-md px-4 py-2 text-sm font-medium text-white transition-colors',
                                danger
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-indigo-600 hover:bg-indigo-700',
                            ]"
                            @click="confirm"
                        >
                            {{ confirmLabel ?? 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
