<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: ''
});
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto px-6 py-10">
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-blue-900">Edit User</h2>
                <Link 
                    :href="route('admin.users.index')"
                    class="text-sm text-blue-600 hover:underline"
                >
                    ← Back to list
                </Link>
            </div>

            <form @submit.prevent="form.put(route('admin.users.update', props.user.id))" class="space-y-6 bg-white p-6 rounded-xl shadow-xl border border-blue-100">
                
                <div>
                    <label class="block mb-1 text-sm font-medium text-blue-700">Name</label>
                    <input 
                        v-model="form.name"
                        type="text"
                        class="w-full border border-blue-300 rounded-xl shadow-sm focus:ring-blue-200 focus:border-blue-400 py-2 px-4"
                    />
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-blue-700">Email</label>
                    <input 
                        v-model="form.email"
                        type="email"
                        class="w-full border border-blue-300 rounded-xl shadow-sm focus:ring-blue-200 focus:border-blue-400 py-2 px-4"
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-blue-700">Role</label>
                    <select 
                        v-model="form.role"
                        class="w-full border border-blue-300 rounded-xl shadow-sm focus:ring-blue-200 focus:border-blue-400 py-2 px-4"
                    >
                        <option value="admin">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                    <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-blue-700">New Password</label>
                    <input 
                        v-model="form.password"
                        type="password"
                        placeholder="Leave blank to keep current password"
                        class="w-full border border-blue-300 rounded-xl shadow-sm focus:ring-blue-200 focus:border-blue-400 py-2 px-4"
                    />
                    <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-blue-700">Confirm Password</label>
                    <input 
                        v-model="form.password_confirmation"
                        type="password"
                        class="w-full border border-blue-300 rounded-xl shadow-sm focus:ring-blue-200 focus:border-blue-400 py-2 px-4"
                    />
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit"
                        class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
