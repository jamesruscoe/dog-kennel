<script setup lang="ts">
/**
 * Reusable dog form — shared by Staff/Dogs/Create, Staff/Dogs/Edit,
 * Owner/Dogs/Create, and Owner/Dogs/Edit.
 *
 * Pass `owners` (staff portal only) to enable owner selection.
 * Pass `showOwnerSelect=false` to hide it (owner portal).
 */
import type { InertiaForm } from '@inertiajs/vue3';
import type { Owner } from '@/types/kennel';

export interface DogFormData {
    owner_id: number | '';
    name: string;
    breed: string;
    date_of_birth: string;
    sex: 'male' | 'female' | '';
    neutered: boolean;
    weight_kg: string;
    colour: string;
    microchip_number: string;
    vet_name: string;
    vet_phone: string;
    vaccination_confirmed: boolean;
    medical_notes: string;
    dietary_notes: string;
    behavioural_notes: string;
}

const props = defineProps<{
    form: InertiaForm<DogFormData>;
    owners?: Owner[];
    showOwnerSelect?: boolean;
    isEditing?: boolean;
    submitLabel?: string;
}>();

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <!-- Owner selector (staff portal) -->
        <div
            v-if="showOwnerSelect !== false && owners && owners.length > 0"
            class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6"
        >
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Owner</h2>
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                    Select Owner <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.owner_id"
                    class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    :class="{ 'border-red-400': form.errors.owner_id }"
                >
                    <option value="">Choose an owner…</option>
                    <option v-for="owner in owners" :key="owner.id" :value="owner.id">
                        {{ owner.name }} ({{ owner.email }})
                    </option>
                </select>
                <p v-if="form.errors.owner_id" class="mt-1 text-xs text-red-500">{{ form.errors.owner_id }}</p>
            </div>
        </div>

        <!-- Basic info -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Dog Details</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :class="{ 'border-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                </div>

                <!-- Breed -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Breed <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.breed"
                        type="text"
                        placeholder="e.g. Labrador Retriever"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :class="{ 'border-red-400': form.errors.breed }"
                    />
                    <p v-if="form.errors.breed" class="mt-1 text-xs text-red-500">{{ form.errors.breed }}</p>
                </div>

                <!-- Date of birth -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Date of Birth</label>
                    <input
                        v-model="form.date_of_birth"
                        type="date"
                        :max="new Date().toISOString().split('T')[0]"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-500">{{ form.errors.date_of_birth }}</p>
                </div>

                <!-- Sex -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                        Sex <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.sex"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        :class="{ 'border-red-400': form.errors.sex }"
                    >
                        <option value="">Select…</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <p v-if="form.errors.sex" class="mt-1 text-xs text-red-500">{{ form.errors.sex }}</p>
                </div>

                <!-- Weight -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Weight (kg)</label>
                    <input
                        v-model="form.weight_kg"
                        type="number"
                        step="0.1"
                        min="0"
                        max="200"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.weight_kg" class="mt-1 text-xs text-red-500">{{ form.errors.weight_kg }}</p>
                </div>

                <!-- Colour -->
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Colour / Markings</label>
                    <input
                        v-model="form.colour"
                        type="text"
                        placeholder="e.g. Golden with white chest"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>

                <!-- Neutered -->
                <div class="flex items-center gap-3 pt-2">
                    <input
                        id="neutered"
                        v-model="form.neutered"
                        type="checkbox"
                        class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="neutered" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Neutered / Spayed</label>
                </div>
            </div>
        </div>

        <!-- Vet & ID -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Vet &amp; Identification</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vet Practice</label>
                    <input
                        v-model="form.vet_name"
                        type="text"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Vet Phone</label>
                    <input
                        v-model="form.vet_phone"
                        type="tel"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Microchip Number</label>
                    <input
                        v-model="form.microchip_number"
                        type="text"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div class="flex items-center gap-3 pt-2">
                    <input
                        id="vaccination_confirmed"
                        v-model="form.vaccination_confirmed"
                        type="checkbox"
                        class="h-4 w-4 rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label for="vaccination_confirmed" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Vaccinations confirmed</label>
                </div>
            </div>
        </div>

        <!-- Care notes -->
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Care Notes</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Medical / Health Notes</label>
                    <textarea
                        v-model="form.medical_notes"
                        rows="3"
                        placeholder="Allergies, medications, health conditions…"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.medical_notes" class="mt-1 text-xs text-red-500">{{ form.errors.medical_notes }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Dietary Requirements</label>
                    <textarea
                        v-model="form.dietary_notes"
                        rows="3"
                        placeholder="Feeding schedule, dietary restrictions, treats…"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.dietary_notes" class="mt-1 text-xs text-red-500">{{ form.errors.dietary_notes }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Behavioural Notes</label>
                    <textarea
                        v-model="form.behavioural_notes"
                        rows="3"
                        placeholder="Temperament, triggers, separation anxiety, dog friendliness…"
                        class="block w-full rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.behavioural_notes" class="mt-1 text-xs text-red-500">{{ form.errors.behavioural_notes }}</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <slot name="cancel" />
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                {{ submitLabel ?? (isEditing ? 'Save Changes' : 'Add Dog') }}
            </button>
        </div>
    </form>
</template>
