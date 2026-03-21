<script setup>
import { ref, computed, nextTick } from 'vue';
import GuestLayout from '@/shared/layouts/GuestLayout.vue';
import PrimaryButton from '@/shared/PrimaryButton.vue';
import InputError from '@/shared/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

// Code input refs
const inputs = ref([]);
const digits = ref(['', '', '', '', '', '']);

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const verificationCodeSent = computed(
    () => props.status === 'verification-code-sent',
);

const setInputRef = (el, index) => {
    if (el) inputs.value[index] = el;
};

const handleInput = (index) => {
    const value = digits.value[index];

    // Only allow digits
    if (!/^\d$/.test(value)) {
        digits.value[index] = '';
        return;
    }

    // Move to next input
    if (value && index < 5) {
        nextTick(() => {
            inputs.value[index + 1]?.focus();
        });
    }
};

const handleKeydown = (event, index) => {
    if (event.key === 'Backspace' && !digits.value[index] && index > 0) {
        nextTick(() => {
            inputs.value[index - 1]?.focus();
        });
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    for (let i = 0; i < 6; i++) {
        digits.value[i] = pasted[i] || '';
    }
    // Focus the next empty input or the last one
    const nextEmpty = digits.value.findIndex(d => !d);
    const focusIndex = nextEmpty === -1 ? 5 : nextEmpty;
    nextTick(() => {
        inputs.value[focusIndex]?.focus();
    });
};

const submitCode = () => {
    form.code = digits.value.join('');
    form.post(route('verification.code.submit'), {
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            nextTick(() => {
                inputs.value[0]?.focus();
            });
        },
    });
};

const resendCode = () => {
    resendForm.post(route('verification.send'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Verificar Email" />

        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            ¡Gracias por registrarte! Hemos enviado un código de verificación
            de 6 dígitos a tu correo electrónico. Ingrésalo a continuación
            para activar tu cuenta.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600 dark:text-green-400"
            v-if="verificationCodeSent"
        >
            Se ha enviado un nuevo código de verificación a tu correo electrónico.
        </div>

        <form @submit.prevent="submitCode">
            <!-- OTP Input -->
            <div class="flex justify-center gap-2 mb-4">
                <input
                    v-for="(digit, index) in digits"
                    :key="index"
                    :ref="(el) => setInputRef(el, index)"
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    class="w-12 h-14 text-center text-xl font-bold border border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                    v-model="digits[index]"
                    @input="handleInput(index)"
                    @keydown="handleKeydown($event, index)"
                    @paste="handlePaste"
                    :autofocus="index === 0"
                />
            </div>

            <InputError class="mb-4 text-center" :message="form.errors.code" />

            <div class="flex flex-col gap-3 items-center">
                <PrimaryButton
                    class="w-full justify-center"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Verificar
                </PrimaryButton>

                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        @click="resendCode"
                        :disabled="resendForm.processing"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                        :class="{ 'opacity-25': resendForm.processing }"
                    >
                        Reenviar código
                    </button>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >Cerrar sesión</Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
