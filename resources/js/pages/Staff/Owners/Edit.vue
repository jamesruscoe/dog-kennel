<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import OwnerForm from '@/Components/Kennel/OwnerForm.vue';
import type { OwnerFormData } from '@/Components/Kennel/OwnerForm.vue';
import type { Owner } from '@/types/kennel';

defineOptions({ layout: KennelLayout });

const props = defineProps<{ owner: Owner }>();

const form = useForm<OwnerFormData>({
    name: props.owner.name,
    email: props.owner.email,
    phone: props.owner.phone ?? '',
    address: props.owner.address ?? '',
    emergency_contact_name: props.owner.emergency_contact_name ?? '',
    emergency_contact_phone: props.owner.emergency_contact_phone ?? '',
    notes: props.owner.notes ?? '',
});

function submit() {
    form.patch(route('staff.owners.update', props.owner.id));
}
</script>

<template>
    <Head :title="`Edit — ${owner.name}`" />

    <PageHeader
        :title="`Edit ${owner.name}`"
        :breadcrumbs="[
            { label: 'Owners', href: route('staff.owners.index') },
            { label: owner.name, href: route('staff.owners.show', owner.id) },
            { label: 'Edit' },
        ]"
    />

    <div class="max-w-2xl">
        <OwnerForm :form="form" :is-editing="true" @submit="submit">
            <template #cancel>
                <Link
                    :href="route('staff.owners.show', owner.id)"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 transition-colors"
                >
                    Cancel
                </Link>
            </template>
        </OwnerForm>
    </div>
</template>
