<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
});

const successMessage = ref('');
const errorMessage = ref('');
const emailError = ref('');

function submit() {
    successMessage.value = 'تم حفظ التعديلات بنجاح!';

    if (!form.name || !form.email || !form.role) {
        errorMessage.value = 'جميع الحقول مطلوبة!';
        successMessage.value = '';
        return;
    }

    if (form.email === props.user.email) {
        emailError.value = '';
        return updateUser();
    }

    axios
        .get(route('admin.users.checkEmail', { email: form.email }))
        .then(res => {
            if (res.data.exists) {
                emailError.value = 'البريد الإلكتروني مستخدم من قبل!';
                successMessage.value = '';
            } else {
                emailError.value = '';
                updateUser();
            }
        })
        .catch(() => {
            emailError.value = 'حدث خطأ أثناء التحقق من البريد الإلكتروني!';
            successMessage.value = '';
        });
}

function updateUser() {
    form.put(route('admin.users.update', props.user.id), {
        onFinish: () => {
            successMessage.value = 'تم حفظ التعديلات بنجاح!';
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit User" />

        <!-- Alerts -->
        <div
            v-for="(msg, type) in { successMessage, errorMessage, emailError }"
            v-if="msg"
            :key="type"
            class="fixed top-4 inset-x-4 sm:left-1/2 sm:-translate-x-1/2 sm:inset-x-auto
                   w-auto sm:max-w-lg text-white py-3 px-4 sm:px-6 rounded-lg shadow-lg
                   flex items-center justify-center z-50"
            :class="{
                'bg-green-500': type === 'successMessage',
                'bg-red-500': type === 'errorMessage',
                'bg-yellow-500': type === 'emailError'
            }"
        >
            <span class="text-sm sm:text-base">{{ msg }}</span>
        </div>

        <!-- Page Wrapper -->
        <div class="px-4 sm:px-6 lg:px-0">
            <div
                class="max-w-2xl mx-auto mt-16 sm:mt-20 p-6 sm:p-10
                       bg-[#1e293b] rounded-2xl sm:rounded-3xl
                       shadow-2xl border border-indigo-100 form-card-prominent"
            >
                <!-- Header -->
                <div class="flex items-center mb-6 sm:mb-8 border-b pb-4 border-indigo-200">
                    <i class="fas fa-user-edit text-2xl sm:text-3xl text-indigo-700 mr-3"></i>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">
                        Edit User
                    </h2>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">
                    <div>
                        <label class="block mb-2 font-bold text-gray-300">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full input-field-prominent"
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold text-gray-300">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full input-field-prominent"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                        <div v-if="emailError" class="text-yellow-500 text-sm mt-1">
                            {{ emailError }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold text-gray-300">Role</label>
                        <select
                            v-model="form.role"
                            class="w-full input-field-prominent select-field-prominent"
                        >
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 sm:py-4 mt-4 sm:mt-6
                               text-lg sm:text-xl font-extrabold
                               rounded-xl transition-all duration-300
                               prominent-blue-button"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Changes</span>
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.form-card-prominent {
    box-shadow: 0 15px 35px rgba(49, 46, 129, 0.2),
                0 5px 15px rgba(0, 0, 0, 0.05);
}

.input-field-prominent {
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    background-color: #1e293b;
    border: 2px solid #d1d5db;
    transition: all 0.3s;
    font-size: 0.95rem;
}

@media (min-width: 640px) {
    .input-field-prominent {
        font-size: 1rem;
    }
}

.input-field-prominent:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.4);
}

.select-field-prominent {
    appearance: none;
    padding-right: 3rem;
}

.prominent-blue-button {
    background: linear-gradient(90deg, #4f46e5, #3b82f6);
    color: white;
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6);
}

.prominent-blue-button:hover {
    background: linear-gradient(90deg, #3730a3, #1d4ed8);
}
</style>
