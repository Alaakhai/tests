<script setup>
/*
  Index.vue for: resources/js/Pages/admin/Schedules/Index.vue
  - Uses AuthenticatedLayout (shared layout)
  - Uses Inertia and Inertia hooks (usePage / useForm)
  - Expects props from server: schedules (paginator), courses (array), filters (object), can (object)
  - Robust to absence of Ziggy and safe access to page props to avoid runtime errors
*/

import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

// Props passed from Laravel controller (with sensible defaults)
const props = defineProps({
  schedules: { type: Object, default: () => ({ data: [], links: [] }) },
  courses: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ course: '', day: '', q: '' }) },
  can: { type: Object, default: () => ({ create: true, edit: true, delete: true }) },
})

// Access shared page (Inertia) - may have different shapes depending on timing; use safe accessor
const page = usePage()

function getPageProps() {
  try {
    if (page && page.props && typeof page.props === 'object') {
      if ('value' in page.props) {
        return page.props.value || {}
      }
      return page.props || {}
    }
  } catch (e) {}
  return {}
}

// Safe computed helpers
const flash = computed(() => {
  const p = getPageProps()
  return (p.flash) ? p.flash : {}
})

const authUser = computed(() => {
  const p = getPageProps()
  return p.auth?.user ?? p.auth ?? null
})

// Local reactive form state for filters
const filterForm = useForm({
  course: props.filters.course ?? '',
  day: props.filters.day ?? '',
  q: props.filters.q ?? '',
})

// UI state
const confirming = ref(false)
const toDeleteId = ref(null)
const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']

// New UI-only state for dropdown menus
const courseMenuOpen = ref(false)
const dayMenuOpen = ref(false)
const courseBtnRef = ref(null)
const dayBtnRef = ref(null)

// Derived/computed values
const schedules = computed(() => props.schedules || { data: [], links: [] })
const coursesList = computed(() => props.courses || [])
const canCreate = computed(() => props.can?.create ?? (authUser.value?.role === 'admin'))
const canEdit = computed(() => props.can?.edit ?? (authUser.value?.role === 'admin'))
const canDelete = computed(() => props.can?.delete ?? (authUser.value?.role === 'admin'))

/** Helper: build URL using Ziggy `route()` if available, otherwise fallback to literal paths. */
function urlFor(name, params = null) {
  try {
    // eslint-disable-next-line no-undef
    return typeof route === 'function' ? route(name, params) : fallbackFor(name, params)
  } catch (e) {
    return fallbackFor(name, params)
  }
}

/** Fallback simple router */
function fallbackFor(name, params = null) {
  switch (name) {
    case 'admin.schedules.index':
      return '/admin/schedules'
    case 'admin.schedules.create':
      return '/admin/schedules/create'
    case 'admin.schedules.edit':
      return `/admin/schedules/${typeof params === 'object' ? params.id ?? '' : params}/edit`
    case 'admin.schedules.destroy':
      return `/admin/schedules/${typeof params === 'object' ? params.id ?? '' : params}`
    case 'admin.schedules.export':
      return '/admin/schedules/export'
    default:
      return '/admin/schedules'
  }
}

// Functions
function applyFilters(e) {
  e && e.preventDefault && e.preventDefault()
  const target = urlFor('admin.schedules.index')

  if (typeof filterForm.get === 'function') {
    filterForm.get(target, { preserveState: true, replace: true })
    return
  }

  Inertia.get(target, {
    course: filterForm.course,
    day: filterForm.day,
    q: filterForm.q,
  }, {
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
  toDeleteId.value = id
  confirming.value = true
}
function cancelDelete() {
  confirming.value = false
  toDeleteId.value = null
}
function deleteNow() {
  if (!toDeleteId.value) return
  const target = urlFor('admin.schedules.destroy', toDeleteId.value)
  Inertia.delete(target, {
    onFinish: () => {
      confirming.value = false
      toDeleteId.value = null
    },
    onError: (err) => {
      console.error('Delete error', err)
    }
  })
}

// Simple pagination navigation using Laravel's links (full URLs)
function goto(url) {
  if (!url) return
  Inertia.visit(url, { preserveState: true })
}

// Export CSV
function exportCsv() {
  const url = urlFor('admin.schedules.export')
  window.location.href = url
}

/* ----- UI helpers for dropdown menus ----- */
function toggleCourseMenu() {
  courseMenuOpen.value = !courseMenuOpen.value
  if (courseMenuOpen.value) dayMenuOpen.value = false
}
function toggleDayMenu() {
  dayMenuOpen.value = !dayMenuOpen.value
  if (dayMenuOpen.value) courseMenuOpen.value = false
}
function selectCourse(val) {
  filterForm.course = val
  courseMenuOpen.value = false
}
function selectDay(val) {
  filterForm.day = val
  dayMenuOpen.value = false
}

// Close menus if click outside
function onDocumentClick(e) {
  const cEl = courseBtnRef.value
  const dEl = dayBtnRef.value
  if (cEl && !cEl.contains(e.target)) courseMenuOpen.value = false
  if (dEl && !dEl.contains(e.target)) dayMenuOpen.value = false
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick)
})
</script>

<template>
  <Head title="Schedule Management" />
  <AuthenticatedLayout>
    <!-- Header -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">Schedule Management</h2>
          <p class="text-sm text-gray-500">Manage course schedules — create, edit, delete and filter.</p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="canCreate"
            :href="urlFor('admin.schedules.create')"
            class="modern-button inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:-translate-y-0.5"
          >
            <!-- plus icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd"
                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Create Schedule
          </Link>

          <button
            @click="exportCsv"
            class="neutral-button inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
          >
            <!-- download icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                 viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 3a1 1 0 011 1v9.586l2.293-2.293a1 1 0 111.414 1.414l-4.007 4.007a1 1 0 01-1.414 0L7.279 12.707a1 1 0 111.414-1.414L11 12.586V4a1 1 0 011-1z"/>
              <path d="M5 20a1 1 0 011-1h12a1 1 0 110 2H6a1 1 0 01-1-1z"/>
            </svg>
            Export CSV
          </button>
        </div>
      </div>
    </template>

    <!-- Quick Apply (kept) -->
    <div class="mb-4 rounded-lg bg-white p-4 shadow">
      <form @submit.prevent="applyFilters" class="flex items-center gap-2">
        <button type="submit" class="modern-button w-full rounded-md px-4 py-2 font-semibold text-white hover:opacity-95">
          Apply Filters
        </button>
      </form>
    </div>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
      <div class="space-y-6 p-6">
        <!-- Flash messages -->
        <div v-if="flash.success" class="rounded bg-green-50 p-3 text-green-800">
          {{ flash.success }}
        </div>
        <div v-if="flash.error" class="rounded bg-red-50 p-3 text-red-800">
          {{ flash.error }}
        </div>

        <!-- Filters -->
        <div class="rounded bg-white p-4 shadow">
          <form @submit.prevent="applyFilters" class="flex flex-wrap items-end gap-4">
            <!-- Course dropdown -->
            <div class="relative" ref="courseBtnRef">
              <button type="button" @click="toggleCourseMenu"
                      class="filter-control min-w-[140px] rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-800">
                <span class="truncate text-sm text-gray-600 dark:text-slate-200">Course</span>
                <span class="ml-2 truncate text-sm text-gray-700 dark:text-slate-300" v-if="filterForm.course">
                  {{ (coursesList.find(c => String(c.id) === String(filterForm.course))?.name) ?? 'Selected' }}
                </span>
                <span class="ml-2 text-sm text-gray-500 dark:text-slate-400" v-else>All</span>
                <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none">
                  <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <ul v-if="courseMenuOpen"
                  class="filter-dropdown absolute z-40 mt-2 max-h-56 w-56 overflow-auto rounded border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <li class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="selectCourse('')">
                  <span class="text-sm">All</span>
                  <span v-if="filterForm.course === ''">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>

                <li v-for="c in coursesList" :key="c.id"
                    class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="selectCourse(c.id)">
                  <span class="truncate text-sm">{{ c.name }}</span>
                  <span v-if="String(filterForm.course) === String(c.id)">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>
              </ul>
            </div>

            <!-- Day dropdown -->
            <div class="relative" ref="dayBtnRef">
              <button type="button" @click="toggleDayMenu"
                      class="filter-control min-w-[120px] rounded-lg bg-slate-100 px-3 py-2 dark:bg-slate-800">
                <span class="truncate text-sm text-gray-600 dark:text-slate-200">Day</span>
                <span class="ml-2 truncate text-sm text-gray-700 dark:text-slate-300" v-if="filterForm.day">{{ filterForm.day }}</span>
                <span class="ml-2 text-sm text-gray-500 dark:text-slate-400" v-else>All</span>
                <svg class="ml-2 h-4 w-4" viewBox="0 0 24 24" fill="none">
                  <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <ul v-if="dayMenuOpen"
                  class="filter-dropdown absolute z-40 mt-2 max-h-56 w-44 overflow-auto rounded border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <li class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="selectDay('')">
                  <span class="text-sm">All</span>
                  <span v-if="filterForm.day === ''">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>

                <li v-for="d in days" :key="d"
                    class="flex cursor-pointer items-center justify-between rounded px-2 py-1 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="selectDay(d)">
                  <span class="text-sm">{{ d }}</span>
                  <span v-if="filterForm.day === d">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/>
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>
              </ul>
            </div>

            <!-- Search -->
            <div class="min-w-[160px] flex-1 max-w-[320px]">
              <div class="relative">
                <input
                  v-model="filterForm.q"
                  type="text"
                  placeholder="Course name or note"
                  class="search-input w-full rounded border bg-white px-3 py-2 pr-10 text-sm dark:bg-slate-800"
                />
                <svg class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2" viewBox="0 0 24 24" fill="none">
                  <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.6" fill="none"/>
                </svg>
              </div>
            </div>

            <div class="flex gap-2">
              <button type="submit" class="modern-button rounded px-4 py-2 text-sm font-semibold text-white">Apply</button>
              <button type="button" @click="resetFilters" class="neutral-button rounded px-3 py-2 text-sm">Reset</button>
            </div>
          </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg bg-white shadow">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Course</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Day</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Start</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">End</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Note</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="s in schedules.data" :key="s.id" class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">{{ s.course?.name ?? '-' }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ s.day_of_week }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ s.start_time }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ s.end_time }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ s.note ?? '-' }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <!-- ✅ Edit button (نفس تنسيق User/Student Management) -->
                    <Link
                      v-if="canEdit"
                      :href="urlFor('admin.schedules.edit', s.id)"
                      class="modern-edit-button inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white shadow-md transition"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                           viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 17.25V20h2.75l8.086-8.086-2.75-2.75L4 17.25zM18.71 8.04a1.003 1.003 0 000-1.42l-1.33-1.33a1.003 1.003 0 00-1.42 0l-1.12 1.12 2.75 2.75 1.12-1.12z"/>
                      </svg>
                      Edit
                    </Link>

                    <!-- ✅ Delete button (نفس تنسيق User/Student Management) -->
                    <button
                      v-if="canDelete"
                      @click="confirmDelete(s.id)"
                      class="modern-delete-button inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-white shadow-md transition"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                           viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 7h12l-1 14H7L6 7zm3-3h6l1 3H8l1-3z"/>
                      </svg>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!schedules.data || schedules.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  No schedules found.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="flex justify-end p-4">
            <nav v-if="schedules.links" class="flex items-center gap-2">
              <template v-for="link in schedules.links" :key="link.label">
                <button
                  v-if="link.url"
                  v-html="link.label"
                  @click.prevent="goto(link.url)"
                  class="rounded border px-3 py-1 text-sm"
                  :class="{ 'bg-blue-600 text-white border-blue-600': link.active }"
                ></button>
                <span v-else class="px-2 text-gray-400 text-sm" v-html="link.label"></span>
              </template>
            </nav>
          </div>
        </div>

        <!-- Confirm modal -->
        <div v-if="confirming" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
          <div class="w-full max-w-sm rounded bg-white p-6 shadow">
            <h3 class="mb-2 font-semibold">Confirm delete</h3>
            <p class="mb-4 text-sm text-gray-600">Are you sure you want to delete this schedule?</p>
            <div class="flex justify-end gap-2">
              <button @click="cancelDelete" class="neutral-button rounded px-4 py-2">Cancel</button>
              <button @click="deleteNow" class="modern-delete-button rounded px-4 py-2 text-white">Delete</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* ====== Global table tweaks (keep original feel) ====== */
table { border-collapse: collapse; width: 100%; }
th { font-weight: 600; color: #1f2937; text-align: left; }
td { vertical-align: middle; }

/* ====== Filter controls ====== */
.filter-control { display: inline-flex; align-items: center; gap: 0.5rem; }
.filter-control .truncate { max-width: 8rem; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

ul { list-style: none; margin: 0; padding: 0; }
.filter-dropdown { box-shadow: 0 6px 18px rgba(2,6,23,0.08); }

.filter-icon { width: 1.25rem; height: 1.25rem; display:inline-flex; align-items:center; justify-content:center; color: #64748b; }
.filter-icon-small { width: 1rem; height: 1rem; color: #94a3b8; }

button[type="submit"] { transition: background-color .15s ease; }
button[type="button"].filter-control { cursor: pointer; }

/* ====== Responsive ====== */
@media (max-width: 640px) {
  .filter-control { min-width: 120px; }
  .search-input { max-width: 220px; }
}

/* ====== Shared button styles to match your system ====== */
/* Primary gradient button (Create / Apply / etc.) */
.modern-button {
  background-image: linear-gradient(to right, #4F46E5 0%, #3B82F6 100%); /* Indigo-600 → Blue-500 */
  box-shadow: 0 4px 10px rgba(79, 70, 229, 0.4);
  color: #fff;
}
.modern-button:hover { background-image: linear-gradient(to right, #4338CA 0%, #2563EB 100%) }

/* Neutral button */
.neutral-button {
  border: 1px solid #e5e7eb; /* gray-200 */
  background: #fff;
  color: #374151; /* gray-700 */
}

/* Edit button gradient (same as User/Student Management) */
.modern-edit-button{
  background-image: linear-gradient(to right, #3B82F6 0%, #60A5FA 100%); /* Blue-500 → Blue-400 */
  box-shadow: 0 2px 5px rgba(59,130,246,.4);
  color: #fff;
}
.modern-edit-button:hover{ background-image: linear-gradient(to right, #2563EB 0%, #3B82F6 100%) }

/* Delete button gradient (same as User/Student Management) */
.modern-delete-button{
  background-image: linear-gradient(to right, #EF4444 0%, #F87171 100%); /* Red-500 → Red-400 */
  box-shadow: 0 2px 5px rgba(239,68,68,.4);
  color: #fff;
}
.modern-delete-button:hover{ background-image: linear-gradient(to right, #DC2626 0%, #EF4444 100%) }
</style>
