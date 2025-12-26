<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: Array,
});

const searchQuery = ref('');

function deleteUser(id) {
    if (confirm('هل أنت متأكد من حذف هذا المستخدم؟ هذا الإجراء لا يمكن التراجع عنه.')) {
        router.delete(route('admin.users.destroy', id), {
            onSuccess: () => alert('تم حذف المستخدم بنجاح'),
            onError: () => alert('حدث خطأ أثناء حذف المستخدم'),
        });
    }
}

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users;
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
    );
});
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <div class="p-4 sm:p-6 user-management-container">
            <!-- Header -->
            <div class="flex flex-col mb-6">
                <h2 class="text-2xl sm:text-3xl font-semibold text-gradient tracking-wide mb-2">
                    User Management
                </h2>
            </div>

            <!-- Search + Add -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div class="w-full sm:flex-1">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Search users..."
                        class="w-full border border-blue-200 rounded-lg py-2 px-4 bg-[#1e2a47] text-white placeholder:text-blue-200 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-300 transition-colors duration-300"
                    >
                </div>

                <Link
                    :href="route('admin.users.create')"
                    class="w-full sm:w-auto justify-center bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white font-semibold py-2 px-6 rounded-lg flex items-center shadow-md transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add User
                </Link>
            </div>

            <!-- Table (Desktop / Tablet) -->
            <div class="hidden md:block bg-[#1e2a47] rounded-xl shadow-lg overflow-hidden border border-blue-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Role</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#0f1b29] divide-y divide-blue-100">
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="hover:bg-blue-700/10 transition-colors"
                            >
                                <td class="px-6 py-4 text-sm text-white">{{ user.name }}</td>
                                <td class="px-6 py-4 text-sm text-white">{{ user.email }}</td>
                                <td class="px-6 py-4 text-sm capitalize">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-blue-100 text-blue-800': user.role === 'student',
                                            'bg-indigo-100 text-indigo-800': user.role === 'admin',
                                            'bg-teal-100 text-teal-800': user.role === 'teacher'
                                        }"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('admin.users.edit', user.id)"
                                            class="px-3 py-1.5 text-white text-sm rounded-lg modern-edit-button"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="px-3 py-1.5 text-white text-sm rounded-lg modern-delete-button"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-blue-400">
                                    No users found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cards (Mobile) -->
            <div class="grid gap-4 md:hidden">
                <div
                    v-for="user in filteredUsers"
                    :key="user.id"
                    class="bg-[#1e2a47] border border-blue-100 rounded-xl p-4 shadow"
                >
                    <div class="mb-2">
                        <p class="text-white font-semibold">{{ user.name }}</p>
                        <p class="text-blue-300 text-sm break-all">{{ user.email }}</p>
                    </div>

                    <span
                        class="inline-block mb-3 px-3 py-1 rounded-full text-xs font-medium"
                        :class="{
                            'bg-blue-100 text-blue-800': user.role === 'student',
                            'bg-indigo-100 text-indigo-800': user.role === 'admin',
                            'bg-teal-100 text-teal-800': user.role === 'teacher'
                        }"
                    >
                        {{ user.role }}
                    </span>

                    <div class="flex gap-2">
                        <Link
                            :href="route('admin.users.edit', user.id)"
                            class="flex-1 text-center px-3 py-2 text-white text-sm rounded-lg modern-edit-button"
                        >
                            Edit
                        </Link>
                        <button
                            @click="deleteUser(user.id)"
                            class="flex-1 px-3 py-2 text-white text-sm rounded-lg modern-delete-button"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div v-if="filteredUsers.length === 0" class="text-center text-blue-400 py-6">
                    No users found
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.text-gradient {
    background-image: linear-gradient(to right, #4F46E5, #3B82F6);
    -webkit-background-clip: text;
    color: transparent;
    font-weight: 700;
    letter-spacing: 2px;
}

.modern-edit-button {
    background-image: linear-gradient(to right, #3B82F6, #60A5FA);
}

.modern-delete-button {
    background-image: linear-gradient(to right, #EF4444, #F87171);
}
</style>
