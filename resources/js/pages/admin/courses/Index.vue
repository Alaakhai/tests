<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps({
  courses: { type: Object, required: true },
  filters: { type: Object, default: () => ({ q: '' }) }
})

const searchQuery = ref(props.filters?.q ?? '')

let t = null
watch(searchQuery, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('admin.courses.index'),
      { q: val },
      { preserveState: true, preserveScroll: true, replace: true }
    )
  }, 400)
})

function destroyItem (id) {
  if (!confirm('Are you sure you want to delete this course?')) return
  router.delete(route('admin.courses.destroy', id), { preserveScroll: true })
}
</script>

<template>
  <Head title="Course Management" />

  <AuthenticatedLayout>
    <!-- ❌ حذف أي خلفية كاملة -->
    <div class="p-4 sm:p-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <h2 class="text-2xl sm:text-3xl font-semibold text-gradient tracking-wide">
          Course Management
        </h2>

        <Link
          :href="route('admin.courses.create')"
          class="modern-button px-4 py-2.5 rounded-lg text-white font-semibold shadow-md"
        >
          + Add Course
        </Link>
      </div>

      <!-- Search -->
      <div class="mb-6">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search courses..."
          class="w-full border border-blue-200 rounded-lg py-2.5 px-4
                 bg-[#1e2a47] text-white placeholder:text-blue-200
                 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
        >
      </div>

      <!-- Table -->
      <div class="bg-[#1e2a47] rounded-xl shadow-lg overflow-hidden border border-blue-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-blue-200">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Code</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-blue-100 uppercase">Teacher</th>
                <th class="px-6 py-3 text-center text-sm font-medium text-blue-100 uppercase">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-[#0b1220] divide-y divide-blue-100">
              <tr
                v-for="course in (props.courses?.data ?? [])"
                :key="course.id"
                class="hover:bg-blue-700/10 transition-colors"
              >
                <td class="px-6 py-4 text-sm text-white font-medium">
                  {{ course.name }}
                </td>

                <td class="px-6 py-4 text-sm text-blue-300">
                  {{ course.code }}
                </td>

                <td class="px-6 py-4 text-sm text-blue-300">
                  {{ course.teacher?.name ?? 'N/A' }}
                </td>

                <td class="px-6 py-4">
                  <div class="flex justify-center gap-2">
                    <Link
                      :href="route('admin.courses.edit', course.id)"
                      class="px-3 py-1.5 text-white text-sm rounded-lg modern-edit-button"
                    >
                      Edit
                    </Link>

                    <button
                      @click="destroyItem(course.id)"
                      class="px-3 py-1.5 text-white text-sm rounded-lg modern-delete-button"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="(props.courses?.data ?? []).length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-blue-400">
                  No courses found
                </td>
              </tr>
            </tbody>
          </table>
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
  letter-spacing: 1px;
}

.modern-button {
  background-image: linear-gradient(to right, #4F46E5, #3B82F6);
}

.modern-edit-button {
  background-image: linear-gradient(to right, #3B82F6, #60A5FA);
}

.modern-delete-button {
  background-image: linear-gradient(to right, #EF4444, #F87171);
}
</style>
