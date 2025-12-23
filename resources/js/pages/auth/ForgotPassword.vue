<script setup>
import GuestLayout from '@/layouts/GuestLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <!-- رسالة الحالة -->
        <div
            v-if="status"
            class="mb-4 rounded-xl border border-cyan-400/40 bg-slate-900/70 px-4 py-3 text-sm font-medium text-cyan-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6" novalidate>
            <!-- النص العلوي -->
            <div class="text-center space-y-2 mb-4">
                <h2 class="text-lg font-semibold tracking-wide text-slate-50">
                    Forgot your password?
                </h2>
                <p class="text-sm leading-relaxed text-slate-200/85 max-w-md mx-auto">
                    No problem. Just let us know your email address and we will email you
                    a password reset link that will allow you to choose a new one.
                </p>
            </div>

            <!-- Email -->
            <div>
                <InputLabel
                    for="email"
                    value="Email"
                    class="text-xs font-semibold tracking-wide text-cyan-200"
                />

                <div class="mt-2 relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-xs text-cyan-300/80"
                    >
                        @
                    </span>

                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                        class="block w-full rounded-full border border-cyan-500/50 bg-slate-900/80 px-9 py-2.5 text-sm text-slate-100 shadow-[0_0_12px_rgba(37,99,235,0.45)] outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-400/70"
                    />
                </div>

                <InputError class="mt-2 text-xs text-rose-400" :message="form.errors.email" />
            </div>

            <!-- الزر -->
            <div class="mt-4 flex justify-center">
                 <PrimaryButton
    :disabled="form.processing"
    class="rounded-full bg-gradient-to-r from-indigo-500 via-blue-500 to-cyan-400 
           px-5 py-1.5 text-xs font-semibold uppercase tracking-wide text-white 
           shadow-[0_0_25px_rgba(56,189,248,0.8)] 
           transition hover:shadow-[0_0_35px_rgba(56,189,248,1)] 
           hover:brightness-110 active:scale-95"
    :class="{ 'opacity-25 cursor-not-allowed': form.processing }"
>
    EMAIL PASSWORD RESET LINK
</PrimaryButton>

            </div>
        </form>
    </GuestLayout>
</template>
