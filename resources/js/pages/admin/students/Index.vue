<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  students: Object,
  filters: Object,
})

const search = ref(props.filters?.q || '')
const encodeForm = useForm({})
const deleteForm = useForm({})

const searchStudents = () => {
  router.get(
    route('admin.students.index'),
    { q: search.value },
    { preserveState: true, replace: true }
  )
}

const deleteStudent = (id) => {
  if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
    deleteForm.delete(route('admin.students.destroy', id), {
      preserveScroll: true,
      onSuccess: () => alert('Student deleted successfully.'),
      onError: () => alert('An error occurred while deleting the student.'),
    })
  }
}

const triggerEncoding = () => {
  encodeForm.post(route('admin.students.encode'), {
    preserveState: true,
    onSuccess: () => alert('Face recognition data update triggered successfully.'),
    onError: () => alert('An error occurred while updating face recognition data.'),
  })
}

const getPhotoUrl = (photoPath) => `/storage/${photoPath}`
</script>

<template>
  <Head title="Student Management" />

  <AuthenticatedLayout>
    <!-- ✅ تم حذف bg + min-h-screen -->
    <div class="p-4 sm:p-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <h2 class="text-2xl sm:text-3xl font-semibold text-gradient tracking-wide">
          Student Management
        </h2>

        <div class="flex gap-3">
          <button
            @click="triggerEncoding"
            :disabled="encodeForm.processing"
            class="update-button px-4 py-2.5 rounded-lg text-white font-semibold shadow-md disabled:opacity-60"
          >
            Update Face Data
          </button>

          <Link
            :href="route('admin.students.create')"
            class="modern-button px-4 py-2.5 rounded-lg text-white font-semibold shadow-md"
          >
            Add Student
          </Link>
        </div>
      </div>

      <!-- Search -->
      <div class="flex gap-3 mb-6">
        <input
          type="text"
          v-model="search"
          @keyup.enter="searchStudents"
          placeholder="Search by name or email..."
          class="w-full border border-blue-200 rounded-lg py-2.5 px-4
                 bg-[#1e2a47] text-white placeholder:text-blue-200
                 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
        >
        <button
          @click="searchStudents"
          class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 rounded-lg"
        >
          Search
        </button>
      </div>

      <!-- Table -->
      <div class="hidden md:block bg-[#1e2a47] rounded-xl shadow-lg overflow-hidden border border-blue-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-200">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Photo</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Courses</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-[#0b1220] divide-y divide-blue-100">
              <tr
                v-for="student in (props.students.data ?? props.students)"
                :key="student.id"
                class="hover:bg-blue-700/10 transition-colors"
              >
                <td class="px-6 py-4">
                  <img
                    v-if="student.photos && student.photos.length"
                    :src="getPhotoUrl(student.photos[0].photo_path)"
                    class="h-10 w-10 rounded-full object-cover"
                  >
                  <div
                    v-else
                    class="h-10 w-10 rounded-full bg-blue-900/40 flex items-center justify-center text-xs text-blue-300"
                  >
                    N/A
                  </div>
                </td>

                <td class="px-6 py-4 text-sm text-white font-medium">
                  {{ student.name }}
                </td>

                <td class="px-6 py-4 text-sm text-blue-300">
                  {{ student.email }}
                </td>

                <td class="px-6 py-4 text-sm text-blue-300">
                  {{ (student.courses || []).length }}
                </td>

                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <Link
                      :href="route('admin.students.edit', student.id)"
                      class="px-3 py-1.5 text-white text-sm rounded-lg modern-edit-button"
                    >
                      Edit
                    </Link>

                    <button
                      @click="deleteStudent(student.id)"
                      class="px-3 py-1.5 text-white text-sm rounded-lg modern-delete-button"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="(props.students.data ?? props.students).length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-blue-400">
                  No students found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="props.students.links"
        class="p-5 flex justify-center gap-2 bg-[#1e2a47] mt-4 rounded-xl"
      >
        <Link
          v-for="(link, i) in props.students.links"
          :key="i"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-4 py-2 rounded-lg text-sm font-semibold"
          :class="{
            'bg-blue-600 text-white': link.active,
            'text-blue-300 hover:bg-blue-700/20': !link.active,
            'opacity-40 pointer-events-none': !link.url
          }"
          preserve-state
          preserve-scroll
        />
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
  letter-spacing: 1px;
}

.modern-button {
  background-image: linear-gradient(to right, #4F46E5, #3B82F6);
}

.update-button {
  background-image: linear-gradient(to right, #10B981, #059669);
}

.modern-edit-button {
  background-image: linear-gradient(to right, #3B82F6, #60A5FA);
}

.modern-delete-button {
  background-image: linear-gradient(to right, #EF4444, #F87171);
}
</style>
