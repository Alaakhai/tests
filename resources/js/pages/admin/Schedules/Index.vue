<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

/* ===================== Props ===================== */
const props = defineProps({
  schedules: { type: Object, default: () => ({ data: [], links: [] }) },
  courses:   { type: Array,  default: () => [] },
  filters:   { type: Object, default: () => ({ course: '', course_id: '', day: '', q: '', search: '' }) },
  can:       { type: Object, default: () => ({ create: true, edit: true, delete: true }) },
})

/* ===================== Page / Auth ===================== */
const page = usePage()
const authUser = computed(() => page.props?.auth?.user ?? null)

/* ===================== Filters ===================== */
const filterForm = useForm({
  course: props.filters.course ?? props.filters.course_id ?? '',
  day:    props.filters.day ?? '',
  q:      props.filters.q ?? props.filters.search ?? '',
})

/* ===================== UI State ===================== */
const confirming = ref(false)
const toDeleteId = ref(null)

const days = ['Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday']

const courseMenuOpen = ref(false)
const dayMenuOpen    = ref(false)
const courseBtnRef   = ref(null)
const dayBtnRef      = ref(null)

/* ===================== Computed ===================== */
const schedules   = computed(() => props.schedules || { data: [], links: [] })
const coursesList = computed(() => props.courses || [])

const canCreate = computed(() => props.can?.create ?? authUser.value?.role === 'admin')
const canEdit   = computed(() => props.can?.edit   ?? authUser.value?.role === 'admin')
const canDelete = computed(() => props.can?.delete ?? authUser.value?.role === 'admin')

/* ===================== Helpers ===================== */
function urlFor(name, params = null) {
  try {
    return typeof route === 'function' ? route(name, params) : '/admin/schedules'
  } catch {
    return '/admin/schedules'
  }
}

function buildParams() {
  return Object.fromEntries(
    Object.entries({
      course_id: filterForm.course || undefined,
      day:       filterForm.day || undefined,
      search:    filterForm.q || undefined,
    }).filter(([_, v]) => v)
  )
}

/* ===================== Actions ===================== */
function applyFilters() {
  Inertia.get(urlFor('admin.schedules.index'), buildParams(), {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  filterForm.course = ''
  filterForm.day = ''
  filterForm.q = ''
  applyFilters()
}

function confirmDelete(id) {
  confirming.value = true
  toDeleteId.value = id
}

function cancelDelete() {
  confirming.value = false
  toDeleteId.value = null
}

function deleteNow() {
  Inertia.delete(urlFor('admin.schedules.destroy', toDeleteId.value), {
    onFinish: cancelDelete,
  })
}

function exportCsv() {
  const q = new URLSearchParams(buildParams()).toString()
  window.open(urlFor('admin.schedules.export') + (q ? `?${q}` : ''), '_blank')
}

function exportPdf() {
  const q = new URLSearchParams(buildParams()).toString()
  window.open(urlFor('admin.schedules.exportPdf') + (q ? `?${q}` : ''), '_blank')
}

/* ===================== Dropdown ===================== */
function toggleCourseMenu() {
  courseMenuOpen.value = !courseMenuOpen.value
  dayMenuOpen.value = false
}
function toggleDayMenu() {
  dayMenuOpen.value = !dayMenuOpen.value
  courseMenuOpen.value = false
}
function selectCourse(v) {
  filterForm.course = v
  courseMenuOpen.value = false
}
function selectDay(v) {
  filterForm.day = v
  dayMenuOpen.value = false
}

function onDocClick(e) {
  if (courseBtnRef.value && !courseBtnRef.value.contains(e.target)) courseMenuOpen.value = false
  if (dayBtnRef.value && !dayBtnRef.value.contains(e.target)) dayMenuOpen.value = false
}

onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))
</script>

<template>
<Head title="Schedule Management" />

<AuthenticatedLayout>
<div class="p-6 space-y-6">

  <!-- ===================== Page Header ===================== -->
  <div class="flex items-center justify-between">
    <h1 class="schedule-title text-gradient">
      Schedule Management
    </h1>

    <div class="flex gap-3">
      <button @click="exportCsv" class="export-btn">Export CSV</button>
      <button @click="exportPdf" class="export-btn">Export PDF</button>

      <Link
  v-if="canCreate"
  :href="urlFor('admin.schedules.create')"
  class="create-btn"
>
  Create Schedule
</Link>

    </div>
  </div>

  <!-- ===================== Filters ===================== -->
  <div class="rounded-xl border px-4 py-3 bg-gradient-to-r from-[#1e3d6c] to-[#5a6a8b]">
    <form @submit.prevent="applyFilters" class="grid grid-cols-12 gap-x-3 gap-y-2 items-end">

      <div class="col-span-12 md:col-span-3 relative" ref="courseBtnRef">
        <button type="button" @click="toggleCourseMenu" class="filter-input">Course</button>
        <ul v-if="courseMenuOpen" class="dropdown">
          <li @click="selectCourse('')">All</li>
          <li v-for="c in coursesList" :key="c.id" @click="selectCourse(c.id)">
            {{ c.name }}
          </li>
        </ul>
      </div>

      <div class="col-span-12 md:col-span-2 relative" ref="dayBtnRef">
        <button type="button" @click="toggleDayMenu" class="filter-input">Day</button>
        <ul v-if="dayMenuOpen" class="dropdown">
          <li @click="selectDay('')">All</li>
          <li v-for="d in days" :key="d" @click="selectDay(d)">
            {{ d }}
          </li>
        </ul>
      </div>

      <div class="col-span-12 md:col-span-5">
        <input v-model="filterForm.q" placeholder="Search" class="filter-input w-full" />
      </div>

      <div class="col-span-12 md:col-span-2 flex gap-2">
        <button type="submit" class="apply-btn">Apply</button>
        <button type="button" @click="resetFilters" class="reset-btn">Reset</button>
      </div>

    </form>
  </div>

  <!-- ===================== Table ===================== -->
  <div class="bg-[#1e2a47] rounded-xl border overflow-hidden">
    <table class="min-w-full divide-y divide-blue-200">
      <thead class="bg-blue-600">
        <tr>
          <th class="th">Course</th>
          <th class="th">Teacher</th>
          <th class="th">Classroom</th>
          <th class="th">Day</th>
          <th class="th">Start</th>
          <th class="th">End</th>
          <th class="th">Actions</th>
        </tr>
      </thead>

      <tbody class="bg-[#0f1b29] divide-y divide-blue-100">
        <tr v-for="s in schedules.data" :key="s.id" class="hover:bg-blue-700/10">
          <td class="cell">{{ s.course?.name ?? '-' }}</td>
          <td class="cell">{{ s.teacher?.name ?? '-' }}</td>
          <td class="cell">{{ s.classroom?.name ?? '-' }}</td>
          <td class="cell">{{ s.day_of_week }}</td>
          <td class="cell">{{ s.start_time }}</td>
          <td class="cell">{{ s.end_time }}</td>
          <td class="px-6 py-4">
            <div class="flex gap-2">
              <Link v-if="canEdit" :href="urlFor('admin.schedules.edit', s.id)" class="edit-btn">
                Edit
              </Link>
              <button v-if="canDelete" @click="confirmDelete(s.id)" class="delete-btn">
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="!schedules.data.length">
          <td colspan="7" class="px-6 py-8 text-center text-blue-400">
            No schedules found
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
</AuthenticatedLayout>
</template>

<style scoped>
.text-gradient{
  background: linear-gradient(to right, #4F46E5, #3B82F6);
  -webkit-background-clip: text;
  color: transparent;
}

.schedule-title{
  font-size: 1.875rem;
  font-weight:700;
  line-height: 1.2;
  letter-spacing: 2px;
}

.export-btn{
  border: 2px solid #c7d2fe;
  color: #fff;
  padding: .75rem 1.75rem;
  border-radius: .75rem;
  font-size: .875rem;
  font-weight: 600;
  min-width: 140px;
  text-align: center;
}

.filter-input{
  width: 100%;
  padding: .5rem .75rem;
  font-size: .875rem;
  background: #1e2a47;
  color: #fff;
  border-radius: .5rem;
  border: 1px solid #3b82f6;
}

.dropdown{
  position: absolute;
  width: 100%;
  background: #fff;
  border-radius: .5rem;
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
  color: #fff;
}

.apply-btn{
  background: linear-gradient(to right, #22c55e, #16a34a);
  color: #fff;
  padding: .5rem .75rem;
  border-radius: .5rem;
}

.reset-btn{
  background: linear-gradient(to right, #ef4444, #dc2626);
  color: #fff;
  padding: .5rem .75rem;
  border-radius: .5rem;
}

.edit-btn{
  background: linear-gradient(to right, #3B82F6, #60A5FA);
  color: #fff;
  padding: .375rem .75rem;
  border-radius: .5rem;
  font-size: .75rem;
}

.delete-btn{
  background: linear-gradient(to right, #EF4444, #F87171);
  color: #fff;
  padding: .375rem .75rem;
  border-radius: .5rem;
  font-size: .75rem;
}
.create-btn{
  background: linear-gradient(to right, #2563eb, #3b82f6); /* أزرق مثل Add Student */
  color: #ffffff;
  padding: .75rem 1.75rem;
  border-radius: .75rem;
  font-size: .875rem;
  font-weight: 600;
  min-width: 140px;
  text-align: center;
}

.create-btn:hover{
  background: linear-gradient(to right, #1d4ed8, #2563eb);
}

</style>
