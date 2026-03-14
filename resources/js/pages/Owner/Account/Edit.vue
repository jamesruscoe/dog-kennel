<script setup lang="ts">
import KennelLayout from '@/Layouts/KennelLayout.vue';
import PageHeader from '@/Components/Kennel/PageHeader.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useTenantRoute } from '@/composables/useTenantRoute';
import { ref } from 'vue';

defineOptions({ layout: KennelLayout });

const tenantRoute = useTenantRoute();

const props = defineProps<{
    user: {
        name: string;
        email: string;
    };
}>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordUpdated = ref(false);

function updateProfile() {
    profileForm.patch(tenantRoute('owner.account.update-profile'));
}

function updatePassword() {
    passwordForm.patch(tenantRoute('owner.account.update-password'), {
        onSuccess: () => {
            passwordForm.reset();
            passwordUpdated.value = true;
            setTimeout(() => { passwordUpdated.value = false; }, 3000);
        },
    });
}
</script>

<template>
    <Head title="My Account" />

    <PageHeader title="My Account" subtitle="Manage your profile and security settings" />

    <div class="space-y-8 max-w-2xl">
        <!-- Profile Section -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Profile Information</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Update your name and email address.</p>
            </div>

            <form @submit.prevent="updateProfile" class="p-6 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
                    <input
                        id="name"
                        v-model="profileForm.name"
                        type="text"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="profileForm.errors.name" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ profileForm.errors.name }}</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                    <input
                        id="email"
                        v-model="profileForm.email"
                        type="email"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="profileForm.errors.email" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ profileForm.errors.email }}</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="profileForm.processing"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        Save Changes
                    </button>
                    <span v-if="profileForm.recentlySuccessful" class="text-xs text-emerald-600 dark:text-emerald-400">Saved.</span>
                </div>
            </form>
        </div>

        <!-- Password Section -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Change Password</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Ensure your account is using a strong password.</p>
            </div>

            <form @submit.prevent="updatePassword" class="p-6 space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Current Password</label>
                    <input
                        id="current_password"
                        v-model="passwordForm.current_password"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="passwordForm.errors.current_password" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ passwordForm.errors.current_password }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">New Password</label>
                    <input
                        id="password"
                        v-model="passwordForm.password"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <p v-if="passwordForm.errors.password" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ passwordForm.errors.password }}</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm New Password</label>
                    <input
                        id="password_confirmation"
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors disabled:opacity-50"
                    >
                        Update Password
                    </button>
                    <span v-if="passwordUpdated" class="text-xs text-emerald-600 dark:text-emerald-400">Password updated.</span>
                </div>
            </form>
        </div>
    </div>
</template>
