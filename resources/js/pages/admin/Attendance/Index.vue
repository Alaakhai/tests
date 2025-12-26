<script setup>
import { computed, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
  records: Object,
  filters: Object,
  students: Array,
  courses: Array,
  schedules: Array,
  teachers: Array,
  classrooms: Array
})

const form = reactive({
  date:         props.filters?.date ?? '',
  student_id:   props.filters?.student_id ?? '',
  course_id:    props.filters?.course_id ?? '',
  schedule_id:  props.filters?.schedule_id ?? '',
  teacher_id:   props.filters?.teacher_id ?? '',
  classroom_id: props.filters?.classroom_id ?? '',
  is_present:   props.filters?.is_present ?? ''
})

function applyFilters() {
  router.get(route('admin.attendance.index'), { ...form }, {
    preserveScroll: true,
    preserveState: true,
    replace: true
  })
}

function resetFilters() {
  Object.keys(form).forEach(k => form[k] = '')
  applyFilters()
}

/* ===== Export ===== */
function exportCsv() {
  const q = new URLSearchParams({ ...form }).toString()
  window.open(route('admin.attendance.export') + (q ? `?${q}` : ''), '_blank')
}
function exportPdf() {
  const q = new URLSearchParams({ ...form }).toString()
  window.open(route('admin.attendance.exportPdf') + (q ? `?${q}` : ''), '_blank')
}

const data  = computed(() => props.records?.data ?? [])
const links = computed(() => props.records?.links ?? [])

function fmtDate(d) {
  return d ? d.slice(0, 10) : ''
}
</script>

<template>
  <AdminLayout>
    <div class="p-6 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gradient">
          Attendance Records
        </h1>

        <!-- Export buttons -->
        <div class="flex gap-2">
          <button @click="exportCsv" class="export-btn">Export CSV</button>
          <button @click="exportPdf" class="export-btn">Export PDF</button>
        </div>
      </div>

      <!-- Filters -->
      <div class="rounded-xl border px-4 py-4 bg-gradient-to-r from-[#1e3d6c] to-[#5a6a8b]">
        <div class="grid grid-cols-12 gap-x-3 gap-y-3 items-end">

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Date</label>
            <input type="date" v-model="form.date" class="filter-input" />
          </div>

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Student</label>
            <select v-model="form.student_id" class="filter-input">
              <option value="">All</option>
              <option v-for="s in students" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Course</label>
            <select v-model="form.course_id" class="filter-input">
              <option value="">All</option>
              <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Teacher</label>
            <select v-model="form.teacher_id" class="filter-input">
              <option value="">All</option>
              <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Classroom</label>
            <select v-model="form.classroom_id" class="filter-input">
              <option value="">All</option>
              <option v-for="r in classrooms" :key="r.id" :value="r.id">{{ r.name }}</option>
            </select>
          </div>

          <div class="col-span-12 md:col-span-2">
            <label class="filter-label">Status</label>
            <select v-model="form.is_present" class="filter-input">
              <option value="">All</option>
              <option value="1">Present</option>
              <option value="0">Absent</option>
            </select>
          </div>

          <!-- Centered buttons -->
          <div class="col-span-12 flex justify-center gap-3 mt-3">
            <button @click="applyFilters" class="apply-btn">Apply</button>
            <button @click="resetFilters" class="reset-btn">Reset</button>
          </div>

        </div>
      </div>

      <!-- Table -->
      <div class="bg-[#1e2a47] rounded-xl border overflow-hidden">
        <table class="min-w-full divide-y divide-blue-200">
          <thead class="bg-blue-600">
            <tr>
              <th class="th">#</th>
              <th class="th">Date</th>
              <th class="th">Student</th>
              <th class="th">Course</th>
              <th class="th">Teacher</th>
              <th class="th">Classroom</th>
              <th class="th">Status</th>
              <!-- ❌ Check-in column removed -->
            </tr>
          </thead>

          <tbody class="bg-[#0f1b29] divide-y divide-blue-100">
            <tr
              v-for="row in data"
              :key="row.id"
              class="hover:bg-blue-700/10 transition-colors"
            >
              <td class="cell">{{ row.id }}</td>
              <td class="cell">{{ fmtDate(row.attendance_date ?? row.created_at) }}</td>
              <td class="cell">{{ row.student?.name ?? '—' }}</td>
              <td class="cell">{{ row.schedule?.course?.name ?? '—' }}</td>
              <td class="cell">{{ row.schedule?.teacher?.name ?? '—' }}</td>
              <td class="cell">{{ row.schedule?.classroom?.name ?? '—' }}</td>
              <td class="cell">
                <span
                  :class="row.is_present
                    ? 'px-2 py-1 rounded-lg text-xs bg-green-100 text-green-700'
                    : 'px-2 py-1 rounded-lg text-xs bg-red-100 text-red-700'"
                >
                  {{ row.is_present ? 'Present' : 'Absent' }}
                </span>
              </td>
            </tr>

            <tr v-if="!data.length">
              <td colspan="7" class="px-6 py-8 text-center text-blue-400">
                No records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center gap-2">
        <Link
          v-for="(l, i) in links"
          :key="i"
          :href="l.url || '#'"
          class="px-3 py-1 rounded-lg border"
          :class="l.active
            ? 'bg-blue-600 text-white border-blue-600'
            : 'bg-white border-blue-200'"
          v-html="l.label"
          preserve-scroll
          preserve-state
        />
      </div>

    </div>
  </AdminLayout>
</template>

<style scoped>
.text-gradient{
  background: linear-gradient(to right, #4F46E5, #3B82F6);
  -webkit-background-clip: text;
  color: transparent;
  font-weight: 700;
  font-size: 1.875rem;
}

.filter-label{
  font-size: .75rem;
  color: #e5e7eb;
  margin-bottom: .25rem;
}

.filter-input{
  width: 100%;
  padding: .5rem .75rem;
  font-size: .75rem;
  background: #1e2a47;
  color: #ffffff;
  border-radius: .5rem;
  border: 1px solid #3b82f6;
}

.th{
  padding: .75rem 1.5rem;
  font-size: .75rem;
  text-transform: uppercase;
  color: #dbeafe;
}

.cell{
  padding: 1rem 1.5rem;
  font-size: .875rem;
  color: #ffffff;
}

.apply-btn{
  background: linear-gradient(to right, #22c55e, #16a34a);
  color: #fff;
  padding: .5rem 1.5rem;
  border-radius: .5rem;
  font-size: .75rem;
}

.reset-btn{
  background: linear-gradient(to right, #ef4444, #dc2626);
  color: #fff;
  padding: .5rem 1.5rem;
  border-radius: .5rem;
  font-size: .75rem;
}

.export-btn{
  background: transparent;
  border: 2px solid #c7d2fe;
  color: #ffffff;
  padding: .75rem 1.75rem;      /* تكبير الحجم */
  border-radius: .75rem;        /* نفس الانحناء بالصورة */
  font-size: .875rem;           /* خط أوضح */
  font-weight: 600;
  min-width: 140px;             /* نفس عرض الأزرار بالصورة */
  text-align: center;
  transition: all .2s ease;
}

.export-btn:hover{
  background: rgba(99,102,241,0.15);
  border-color: #6366f1;
}

</style>
