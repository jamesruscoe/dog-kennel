<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import DogForm from '@/Components/Kennel/DogForm.vue';
import type { DogFormData } from '@/Components/Kennel/DogForm.vue';
import type { Owner } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{
    owners: Owner[];
    selectedOwner: Owner | null;
}>();

const form = useForm<DogFormData>({
    owner_id: props.selectedOwner?.id ?? '',
    name: '',
    breed: '',
    date_of_birth: '',
    sex: '',
    neutered: false,
    weight_kg: '',
    colour: '',
    microchip_number: '',
    vet_name: '',
    vet_phone: '',
    vaccination_confirmed: false,
    medical_notes: '',
    dietary_notes: '',
    behavioural_notes: '',
});

function submit() {
    form.post(route('staff.dogs.store'));
}
</script>

<template>
    <Head title="Add Dog" />

    <PageHeader
        title="Add Dog"
        :breadcrumbs="[
            { label: 'Dogs', href: route('staff.dogs.index') },
            { label: 'New Dog' },
        ]"
    />

    <div class="max-w-2xl">
        <DogForm :form="form" :owners="owners" @submit="submit">
            <template #cancel>
                <Link
                    :href="selectedOwner ? route('staff.owners.show', selectedOwner.id) : route('staff.dogs.index')"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 transition-colors"
                >
                    Cancel
                </Link>
            </template>
        </DogForm>
    </div>
</template>
