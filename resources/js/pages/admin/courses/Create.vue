<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    teachers: Array,
});

const form = useForm({
    name: '',
    code: '',
    description: '',
    teacher_id: null,
});

const submit = () => {
    form.post(route('admin.courses.store'));
};
</script>

<template>
    <Head title="Add New Course" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-blue-900 leading-tight tracking-wide">
                📘 Add New Course
            </h2>
        </template>

        <!-- الصفحة بخلفية تدرج أزرق هادئ -->
        <div class="py-12 bg-gradient-to-b from-blue-100 via-blue-50 to-blue-200 min-h-screen">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white shadow-lg border border-blue-300 rounded-2xl p-8 transition-transform duration-300 hover:shadow-2xl hover:scale-[1.01]"
                >
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Course Name -->
                        <div>
                            <label
                                for="name"
                                class="block font-semibold text-blue-800 text-sm uppercase tracking-wide"
                            >Course Name</label>
                            <input
                                v-model="form.name"
                                id="name"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-blue-300 focus:ring-2 focus:ring-blue-400 focus:outline-none p-2.5 placeholder-gray-400"
                                placeholder="Enter course name..."
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Course Code -->
                        <div>
                            <label
                                for="code"
                                class="block font-semibold text-blue-800 text-sm uppercase tracking-wide"
                            >Course Code</label>
                            <input
                                v-model="form.code"
                                id="code"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-blue-300 focus:ring-2 focus:ring-blue-400 focus:outline-none p-2.5 placeholder-gray-400"
                                placeholder="Enter course code..."
                            />
                            <div
                                v-if="form.errors.code"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.code }}
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="block font-semibold text-blue-800 text-sm uppercase tracking-wide"
                            >Description</label>
                            <textarea
                                v-model="form.description"
                                id="description"
                                rows="4"
                                class="mt-2 w-full rounded-lg border border-blue-300 focus:ring-2 focus:ring-blue-400 focus:outline-none p-2.5 placeholder-gray-400"
                                placeholder="Write a short description..."
                            ></textarea>
                        </div>

                        <!-- Teacher -->
                        <div>
                            <label
                                for="teacher_id"
                                class="block font-semibold text-blue-800 text-sm uppercase tracking-wide"
                            >Teacher</label>
                            <select
                                v-model="form.teacher_id"
                                id="teacher_id"
                                class="mt-2 w-full rounded-lg border border-blue-300 bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none p-2.5"
                            >
                                <option :value="null" disabled>Select a teacher</option>
                                <option
                                    v-for="teacher in teachers"
                                    :key="teacher.id"
                                    :value="teacher.id"
                                >
                                    {{ teacher.name }}
                                </option>
                            </select>
                            <div
                                v-if="form.errors.teacher_id"
                                class="text-sm text-red-600 mt-1"
                            >
                                {{ form.errors.teacher_id }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end pt-6">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-800 text-white font-semibold rounded-lg shadow-md hover:from-blue-700 hover:to-blue-900 focus:ring-2 focus:ring-blue-300 transition duration-300 ease-in-out disabled:opacity-60"
                            >
                                💾 Save Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
