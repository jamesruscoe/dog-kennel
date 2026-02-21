<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-6 text-center">
            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Confirm your password</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                This is a secure area. Please confirm your password before continuing.
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="Enter your password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-70': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Confirming...' : 'Confirm password' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
