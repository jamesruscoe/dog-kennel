<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const page = usePage();

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    _token: page.props.csrf_token,
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Forgot your password?</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-400"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email address"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-70': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending...' : 'Send reset link' }}
                </PrimaryButton>
            </div>

            <div class="mt-4 text-center">
                <Link
                    :href="route('login')"
                    class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
                >
                    Back to sign in
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
