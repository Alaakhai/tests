<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'student',
});

const successMessage = ref('');
const errorMessage = ref('');

function submit() {
    if (!form.name || !form.email || !form.password) {
        errorMessage.value = 'جميع الحقول مطلوبة!';
        return;
    }

    errorMessage.value = '';
    form.post(route('admin.users.store'), {
        onFinish: () => {
            successMessage.value = 'تمت إضافة المستخدم بنجاح';
        },
    });
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add User" />

        <!-- Alerts -->
        <div
            v-if="successMessage"
            class="fixed top-4 inset-x-4 sm:left-1/2 sm:-translate-x-1/2 sm:inset-x-auto
                   bg-green-500 text-white py-3 px-4 sm:px-6 rounded-lg shadow-lg
                   flex items-center justify-center z-50 sm:max-w-lg"
        >
            {{ successMessage }}
        </div>

        <div
            v-if="errorMessage"
            class="fixed top-4 inset-x-4 sm:left-1/2 sm:-translate-x-1/2 sm:inset-x-auto
                   bg-red-500 text-white py-3 px-4 sm:px-6 rounded-lg shadow-lg
                   flex items-center justify-center z-50 sm:max-w-lg"
        >
            {{ errorMessage }}
        </div>

        <!-- Page Wrapper -->
        <div class="px-4 sm:px-6 lg:px-0">
            <div
                class="max-w-2xl mx-auto mt-16 sm:mt-20
                       p-6 sm:p-8 lg:p-10
                       bg-[#1e293b] rounded-2xl sm:rounded-3xl
                       shadow-2xl border border-indigo-100 form-card-prominent"
            >
                <!-- Header -->
                <div class="flex items-center mb-6 sm:mb-8 border-b pb-4 border-indigo-200">
                    <i class="fas fa-user-plus text-2xl sm:text-3xl text-indigo-700 mr-3"></i>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">
                        Add New User
                    </h2>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5 sm:space-y-6">
                    <div>
                        <label class="block mb-2 font-bold text-gray-300 text-sm sm:text-base">
                            Name
                        </label>
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
                        <label class="block mb-2 font-bold text-gray-300 text-sm sm:text-base">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full input-field-prominent"
                        />
                        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold text-gray-300 text-sm sm:text-base">
                            Password
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full input-field-prominent"
                        />
                        <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 font-bold text-gray-300 text-sm sm:text-base">
                            Role
                        </label>
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
                               prominent-submit-button"
                    >
                        <span v-if="form.processing">Adding...</span>
                        <span v-else>Add User</span>
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
    font-size: 0.95rem;
    transition: all 0.3s;
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

.prominent-submit-button {
    background: linear-gradient(90deg, #4f46e5, #3b82f6);
    color: #fff;
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6);
}

.prominent-submit-button:hover {
    background: linear-gradient(90deg, #3730a3, #1d4ed8);
}
</style>
