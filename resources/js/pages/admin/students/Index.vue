<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  students: Array,
})

const encodeForm = useForm({})

const getPhotoUrl = (photoPath) => `/storage/${photoPath}`

const triggerEncoding = () => {
  encodeForm.post(route('admin.students.encode'), {
    preserveState: true,
    onSuccess: () => {
      alert('Face recognition data update triggered!')
    },
    onError: () => {
      alert('An error occurred while updating.')
    },
  })
}
</script>

<template>
  <Head title="Student Management" />

  <AuthenticatedLayout>
    <!-- Header -->
    <template #header>
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <!-- title + icon -->
        <div class="flex items-center gap-2">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 3l9.5 4.5-3.1 1.3v4.7c0 .6-.3 1.2-.9 1.5-2.1 1.2-4.3 1.8-6.5 1.8s-4.4-.6-6.5-1.8c-.6-.3-.9-.9-.9-1.5V8.8L2.5 7.5 12 3zM6.5 10.3v3.1c1.8.9 3.6 1.4 5.5 1.4s3.7-.5 5.5-1.4v-3.1L12 12.3 6.5 10.3z"/>
            </svg>
          </span>
          <h2 class="text-2xl font-semibold text-blue-900 leading-tight">Student Management</h2>
        </div>

        <!-- right buttons -->
        <div class="flex items-center gap-3">
          <!-- Update face data -->
          <button
            @click="triggerEncoding"
            :disabled="encodeForm.processing"
            class="update-button inline-flex items-center gap-2 rounded-xl px-4 py-2.5 font-semibold text-white shadow-md transition disabled:opacity-70"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 6V3L8 7l4 4V8a4 4 0 014 4 4.002 4.002 0 01-4 4 4.002 4.002 0 01-3.874-3H6.11A6.002 6.002 0 0012 20a6 6 0 000-12z"/>
            </svg>
            Update Face Recognition Data
          </button>

          <!-- Add student -->
          <Link
            :href="route('admin.students.create')"
            class="add-button inline-flex items-center gap-2 rounded-xl px-4 py-2.5 font-semibold text-white shadow-md transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Add Student
          </Link>
        </div>
      </div>
    </template>

    <!-- Table -->
    <div class="overflow-hidden rounded-2xl bg-white shadow-lg sm:rounded-lg">
      <div class="p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-blue-200">
          <thead class="bg-blue-600">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-blue-100">Photo</th>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-blue-100">Name</th>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-blue-100">Email</th>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-blue-100">Courses Enrolled</th>
              <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-blue-100">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-blue-100 bg-white">
            <tr v-for="student in students" :key="student.id" class="transition-colors duration-200 hover:bg-blue-50">
              <td class="whitespace-nowrap px-6 py-4">
                <img
                  v-if="student.photos.length > 0"
                  :src="getPhotoUrl(student.photos[0].photo_path)"
                  alt="Student Photo"
                  class="h-10 w-10 rounded-full object-cover"
                >
                <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-xs text-gray-500">
                  No Photo
                </div>
              </td>

              <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-blue-900">{{ student.name }}</td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-blue-700">{{ student.email }}</td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-blue-700">{{ student.courses.length }}</td>

              <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                <div class="flex gap-2">
                  <a
                    href="#"
                    class="modern-edit-button inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white shadow-md transition"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M4 17.25V20h2.75l8.086-8.086-2.75-2.75L4 17.25zM18.71 8.04a1.003 1.003 0 000-1.42l-1.33-1.33a1.003 1.003 0 00-1.42 0l-1.12 1.12 2.75 2.75 1.12-1.12z"/>
                    </svg>
                    Edit
                  </a>

                  <a
                    href="#"
                    class="modern-delete-button inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white shadow-md transition"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M6 7h12l-1 14H7L6 7zm3-3h6l1 3H8l1-3z"/>
                    </svg>
                    Delete
                  </a>
                </div>
              </td>
            </tr>

            <tr v-if="students.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-blue-400">No students found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* header buttons */
.add-button{
  background-image: linear-gradient(to right,#4F46E5 0%,#3B82F6 100%);
  box-shadow: 0 4px 10px rgba(59,130,246,.3);
}
.add-button:hover{ background-image: linear-gradient(to right,#4338CA 0%,#2563EB 100%) }

.update-button{
  background-image: linear-gradient(to right,#10B981 0%,#059669 100%);
  box-shadow: 0 4px 10px rgba(16,185,129,.3);
}
.update-button:hover{ background-image: linear-gradient(to right,#059669 0%,#047857 100%) }

/* table action buttons */
.modern-edit-button{
  background-image: linear-gradient(to right,#3B82F6 0%,#60A5FA 100%);
  box-shadow: 0 2px 5px rgba(59,130,246,.4);
}
.modern-edit-button:hover{ background-image: linear-gradient(to right,#2563EB 0%,#3B82F6 100%) }

.modern-delete-button{
  background-image: linear-gradient(to right,#EF4444 0%,#F87171 100%);
  box-shadow: 0 2px 5px rgba(239,68,68,.4);
}
.modern-delete-button:hover{ background-image: linear-gradient(to right,#DC2626 0%,#EF4444 100%) }

/* table head */
.min-w-full thead{ background-color:#3b82f6; border-top-left-radius:1rem; border-top-right-radius:1rem }
.min-w-full thead th{ color:#DBEAFE }
</style>
