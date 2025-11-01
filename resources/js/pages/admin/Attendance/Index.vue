<script setup>
import { computed, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
  records: Object,
  filters: Object,
  students: Array,
  courses: Array,
  schedules: Array
})

const form = reactive({
  date:        props.filters?.date ?? '',
  student_id:  props.filters?.student_id ?? '',
  course_id:   props.filters?.course_id ?? '',
  schedule_id: props.filters?.schedule_id ?? '',
  is_present:  props.filters?.is_present ?? ''
})

function applyFilters() {
  router.get(route('admin.attendance.index'), { ...form }, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  })
}

function resetFilters() {
  form.date = ''
  form.student_id = ''
  form.course_id = ''
  form.schedule_id = ''
  form.is_present = ''
  applyFilters()
}

const data  = computed(() => props.records?.data ?? [])
const links = computed(() => props.records?.links ?? [])

function fmtTime(t) {
  if (!t) return ''
  return t.slice(0, 5)
}
function scheduleLabel(sc) {
  if (!sc) return 'Schedule'
  return `${sc.day_of_week ?? ''} ${fmtTime(sc.start_time)}–${fmtTime(sc.end_time)}`
}
function fmtDate(d) {
  if (!d) return ''
  return d.slice(0, 10)
}
</script>

<template>
  <AdminLayout>
    <div class="p-6 space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-blue-900">Attendance Records</h1>
      </div>

      <!-- 🔷 Filters Box (Darker Blue Gradient) -->
      <div
        class="rounded-2xl shadow-lg p-5 border border-blue-400 flex justify-center 
               bg-gradient-to-r from-[#60a5fa] via-[#3b82f6] to-[#2563eb] 
               text-white transition-all duration-300"
      >
        <div class="flex flex-wrap justify-center items-end gap-3 w-full max-w-5xl">

          <!-- Date -->
          <div class="flex flex-col w-[160px]">
            <label class="text-[11px] mb-1 font-semibold text-white">Date</label>
            <input
              v-model="form.date"
              type="date"
              class="h-9 text-xs rounded-lg border border-blue-100 bg-white text-gray-800 px-3 
                     shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
            />
          </div>

          <!-- Student -->
          <div class="flex flex-col w-[160px]">
            <label class="text-[11px] mb-1 font-semibold text-white">Student</label>
            <select
              v-model="form.student_id"
              class="h-9 text-xs rounded-lg border border-blue-100 bg-white text-gray-800 px-3 
                     shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
            >
              <option value="">All</option>
              <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <!-- Course -->
          <div class="flex flex-col w-[160px]">
            <label class="text-[11px] mb-1 font-semibold text-white">Course</label>
            <select
              v-model="form.course_id"
              class="h-9 text-xs rounded-lg border border-blue-100 bg-white text-gray-800 px-3 
                     shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
            >
              <option value="">All</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Schedule -->
          <div class="flex flex-col w-[170px]">
            <label class="text-[11px] mb-1 font-semibold text-white">Schedule</label>
            <select
              v-model="form.schedule_id"
              class="h-9 text-xs rounded-lg border border-blue-100 bg-white text-gray-800 px-3 
                     shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
            >
              <option value="">All</option>
              <option v-for="sc in schedules" :key="sc.id" :value="sc.id">
                {{ scheduleLabel(sc) || ('Schedule #' + sc.id) }}
              </option>
            </select>
          </div>

          <!-- Status -->
          <div class="flex flex-col w-[140px]">
            <label class="text-[11px] mb-1 font-semibold text-white">Status</label>
            <select
              v-model="form.is_present"
              class="h-9 text-xs rounded-lg border border-blue-100 bg-white text-gray-800 px-3 
                     shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-300"
            >
              <option value="">All</option>
              <option value="1">Present</option>
              <option value="0">Absent</option>
            </select>
          </div>

          <!-- 🔘 Buttons -->
          <div class="flex gap-2 mt-1">

            <!-- ✅ Apply Button (Soft Green) -->
            <button
              @click="applyFilters"
              class="h-9 min-w-[85px] rounded-lg bg-gradient-to-r from-green-400 via-emerald-500 to-green-600 
                     text-white font-semibold text-xs px-4 shadow-md hover:shadow-lg 
                     hover:from-green-500 hover:to-emerald-600 active:scale-[0.97] transition-all duration-300"
            >
              <span class="inline-flex items-center gap-1">
                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                  <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4v16m8-8H4'/>
                </svg>
                Apply
              </span>
            </button>

            <!-- ❌ Reset Button (Soft Red) -->
            <button
              @click="resetFilters"
              type="button"
              class="h-9 min-w-[85px] rounded-lg bg-gradient-to-r from-rose-400 via-red-500 to-pink-500 
                     text-white font-semibold text-xs px-4 shadow-md hover:shadow-lg 
                     hover:from-rose-500 hover:to-red-600 active:scale-[0.97] transition-all duration-300"
            >
              <span class="inline-flex items-center gap-1">
                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                  <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/>
                </svg>
                Reset
              </span>
            </button>
          </div>

        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow overflow-x-auto border border-blue-100">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="border-b dark:border-gray-700 bg-blue-600 text-blue-100">
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">#</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Student</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Schedule</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Check-in</th>
              <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider">Check-out</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in data" :key="row.id" class="border-b dark:border-gray-800 odd:bg-white even:bg-[#f6fbff]">
              <td class="px-4 py-3 font-semibold text-blue-900">{{ row.id }}</td>
              <td class="px-4 py-3 text-blue-700">{{ fmtDate(row.attendance_date ?? row.created_at) }}</td>
              <td class="px-4 py-3 text-blue-700">
                <div class="flex items-center gap-2">
                  <span>{{ row.student?.name ?? '—' }}</span>
                  <span class="text-[11px] text-blue-400">(#{{ row.student_id }})</span>
                </div>
              </td>
              <td class="px-4 py-3 text-blue-700">
                {{ row.schedule ? scheduleLabel(row.schedule) : ('Schedule #' + (row.schedule_id ?? '—')) }}
              </td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'px-2 py-1 rounded-lg text-[11px] font-semibold inline-flex items-center justify-center',
                    row.is_present ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                  ]"
                >
                  {{ row.is_present ? 'Present' : 'Absent' }}
                </span>
              </td>
              <td class="px-4 py-3 text-blue-700">{{ row.attended_at ?? '—' }}</td>
              <td class="px-4 py-3 text-blue-700">{{ row.departed_at ?? '—' }}</td>
            </tr>

            <tr v-if="!data.length">
              <td colspan="7" class="px-4 py-8 text-center text-blue-400">
                <div class="flex flex-col items-center">
                  <span class="text-4xl mb-2">🚫</span>
                  <p class="text-lg">No records found.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-wrap items-center gap-2 justify-center">
        <Link
          v-for="(l, i) in links"
          :key="i"
          :href="l.url || '#'"
          class="px-3 py-1 rounded-lg border"
          :class="[
            l.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white dark:bg-gray-900 border-blue-200'
          ]"
          v-html="l.label"
          preserve-scroll
          preserve-state
        />
      </div>
    </div>
  </AdminLayout>
</template>
