<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import DogForm from '@/Components/Kennel/DogForm.vue';
import type { DogFormData } from '@/Components/Kennel/DogForm.vue';
import type { Dog, Owner } from '@/types/kennel';
import { useTenantRoute } from '@/composables/useTenantRoute';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    dog: Dog;
    owners: Owner[];
}>();

const form = useForm<DogFormData>({
    owner_id: props.dog.owner_id,
    name: props.dog.name,
    breed: props.dog.breed,
    date_of_birth: props.dog.date_of_birth ?? '',
    sex: props.dog.sex,
    neutered: props.dog.neutered,
    weight_kg: props.dog.weight_kg?.toString() ?? '',
    colour: props.dog.colour ?? '',
    microchip_number: props.dog.microchip_number ?? '',
    vet_name: props.dog.vet_name ?? '',
    vet_phone: props.dog.vet_phone ?? '',
    vaccination_confirmed: props.dog.vaccination_confirmed,
    medical_notes: props.dog.medical_notes ?? '',
    dietary_notes: props.dog.dietary_notes ?? '',
    behavioural_notes: props.dog.behavioural_notes ?? '',
});

function submit() {
    form.patch(tenantRoute('staff.dogs.update', props.dog.id));
}
</script>

<template>
    <Head :title="`Edit — ${dog.name}`" />

    <PageHeader
        :title="`Edit ${dog.name}`"
        :breadcrumbs="[
            { label: 'Dogs', href: tenantRoute('staff.dogs.index') },
            { label: dog.name, href: tenantRoute('staff.dogs.show', dog.id) },
            { label: 'Edit' },
        ]"
    />

    <div class="max-w-2xl">
        <DogForm :form="form" :owners="owners" :is-editing="true" @submit="submit">
            <template #cancel>
                <Link
                    :href="tenantRoute('staff.dogs.show', dog.id)"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 transition-colors"
                >
                    Cancel
                </Link>
            </template>
        </DogForm>
    </div>
</template>
