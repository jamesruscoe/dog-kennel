<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import OwnerForm from '@/Components/Kennel/OwnerForm.vue';
import type { OwnerFormData } from '@/Components/Kennel/OwnerForm.vue';

defineOptions({ layout: KennelLayout });

const form = useForm<OwnerFormData>({
    name: '',
    email: '',
    phone: '',
    address: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    notes: '',
});

function submit() {
    form.post(route('staff.owners.store'));
}
</script>

<template>
    <Head title="Add Owner" />

    <PageHeader
        title="Add Owner"
        :breadcrumbs="[{ label: 'Owners', href: route('staff.owners.index') }, { label: 'New Owner' }]"
    />

    <div class="max-w-2xl">
        <OwnerForm :form="form" @submit="submit">
            <template #cancel>
                <Link
                    :href="route('staff.owners.index')"
                    class="rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 transition-colors"
                >
                    Cancel
                </Link>
            </template>
        </OwnerForm>
    </div>
</template>
