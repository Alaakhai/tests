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

/**
 * getPageProps()
 * Safely return the page props object whether Inertia exposes it as:
 * - page.props.value (reactive ref), or
 * - page.props (plain), or
 * - fallback to {}
 */
function getPageProps() {
  try {
    if (page && page.props && typeof page.props === 'object') {
      if ('value' in page.props) {
        return page.props.value || {}
      }
      return page.props || {}
    }
  } catch (e) {
    // ignore and fallback
  }
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

// Local reactive form state for filters (we'll use useForm to keep consistency with Inertia)
const filterForm = useForm({
  course: props.filters.course ?? '',
  day: props.filters.day ?? '',
  q: props.filters.q ?? '',
})

// UI state (unchanged core behavior)
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

/**
 * Helper: build URL using Ziggy `route()` if available, otherwise fallback to literal paths.
 */
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

// Apply filters via Inertia GET (preserve state)
function applyFilters(e) {
  e && e.preventDefault && e.preventDefault()

  const target = urlFor('admin.schedules.index')

  // use useForm's get() if available, otherwise fallback to Inertia.get
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

// Reset filters (assign fields directly then apply)
function resetFilters() {
  filterForm.course = ''
  filterForm.day = ''
  filterForm.q = ''
  applyFilters()
}

// Open confirmation modal
function confirmDelete(id) {
  toDeleteId.value = id
  confirming.value = true
}

// Cancel deletion (clean)
function cancelDelete() {
  confirming.value = false
  toDeleteId.value = null
}

// Perform delete (Inertia.delete)
function deleteNow() {
  if (!toDeleteId.value) return
  const target = urlFor('admin.schedules.destroy', toDeleteId.value)

  Inertia.delete(target, {
    onFinish: () => {
      confirming.value = false
      toDeleteId.value = null
    },
    onError: (err) => {
      // optional: show error or log
      console.error('Delete error', err)
    }
  })
}

// Simple pagination navigation using Laravel's links (full URLs)
function goto(url) {
  if (!url) return
  Inertia.visit(url, { preserveState: true })
}

// Export CSV: try to navigate to export route (server must implement it)
function exportCsv() {
  const url = urlFor('admin.schedules.export')
  // if server doesn't implement export route, user will see 404 — implement server side if wanted
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
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Schedule Management</h2>
          <p class="text-sm text-gray-500">Manage course schedules — create, edit, delete and filter.</p>
        </div>

        <div class="flex items-center gap-3">
          <Link
            v-if="canCreate"
            :href="urlFor('admin.schedules.create')"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700"
          >
            + Create Schedule
          </Link>

          <button @click="exportCsv" class="px-3 py-2 border rounded text-sm hover:bg-gray-50">
            Export CSV
          </button>
        </div>
      </div>
    </template>

    <div class="mb-4 p-4 bg-white rounded-lg shadow">
      <!-- top action -->
      <form @submit.prevent="applyFilters" class="flex gap-2 items-center">
        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">
          Apply Filters
        </button>
      </form>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 space-y-6">
        <!-- Flash messages -->
        <div v-if="flash.success" class="p-3 bg-green-50 text-green-800 rounded">
          {{ flash.success }}
        </div>
        <div v-if="flash.error" class="p-3 bg-red-50 text-red-800 rounded">
          {{ flash.error }}
        </div>

        <!-- Filters with dropdowns -->
        <div class="bg-white p-4 rounded shadow">
          <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4 items-end">
            <!-- Course dropdown control -->
            <div class="relative" ref="courseBtnRef">
              <button type="button" @click="toggleCourseMenu" class="filter-control flex items-center gap-2 rounded-lg bg-slate-100 dark:bg-slate-800 px-3 py-2 min-w-[140px]">
                <span class="text-sm text-gray-600 dark:text-slate-200 truncate">Course</span>
                <span class="ml-2 text-sm text-gray-700 dark:text-slate-300 truncate" v-if="filterForm.course">
                  {{ (coursesList.find(c => String(c.id) === String(filterForm.course))?.name) ?? 'Selected' }}
                </span>
                <span class="text-sm text-gray-500 dark:text-slate-400 ml-2" v-else>All</span>
                <!-- chevron SVG -->
                <svg class="ml-2 w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <!-- Dropdown list -->
              <ul v-if="courseMenuOpen" class="absolute z-40 mt-2 w-56 max-h-56 overflow-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded shadow-sm p-2">
                <!-- All with check -->
                <li class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between"
                    @click="selectCourse('')">
                  <div class="flex items-center gap-2">
                    <span class="text-sm">All</span>
                  </div>
                  <span v-if="filterForm.course === ''" class="inline-flex items-center">
                    <!-- checked box SVG -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5" />
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else class="inline-flex items-center">
                    <!-- unchecked box SVG -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>

                <li v-for="c in coursesList" :key="c.id" class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between"
                    @click="selectCourse(c.id)">
                  <div class="flex items-center gap-2">
                    <span class="text-sm truncate">{{ c.name }}</span>
                  </div>
                  <span v-if="String(filterForm.course) === String(c.id)" class="inline-flex items-center">
                    <!-- checked box SVG -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5" />
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else class="inline-flex items-center">
                    <!-- unchecked box SVG -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>
              </ul>
            </div>

            <!-- Day dropdown control -->
            <div class="relative" ref="dayBtnRef">
              <button type="button" @click="toggleDayMenu" class="filter-control flex items-center gap-2 rounded-lg bg-slate-100 dark:bg-slate-800 px-3 py-2 min-w-[120px]">
                <span class="text-sm text-gray-600 dark:text-slate-200 truncate">Day</span>
                <span class="ml-2 text-sm text-gray-700 dark:text-slate-300 truncate" v-if="filterForm.day">{{ filterForm.day }}</span>
                <span class="text-sm text-gray-500 dark:text-slate-400 ml-2" v-else>All</span>
                <!-- chevron SVG -->
                <svg class="ml-2 w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M7 10l5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>

              <!-- Dropdown list -->
              <ul v-if="dayMenuOpen" class="absolute z-40 mt-2 w-44 max-h-56 overflow-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded shadow-sm p-2">
                <li class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between"
                    @click="selectDay('')">
                  <span class="text-sm">All</span>
                  <span v-if="filterForm.day === ''" class="inline-flex items-center">
                    <!-- checked box -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5" />
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else class="inline-flex items-center">
                    <!-- unchecked -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>

                <li v-for="d in days" :key="d" class="px-2 py-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer flex items-center justify-between"
                    @click="selectDay(d)">
                  <span class="text-sm">{{ d }}</span>
                  <span v-if="filterForm.day === d" class="inline-flex items-center">
                    <!-- checked -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5" />
                      <path d="M7 12l3 3 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <span v-else class="inline-flex items-center">
                    <!-- unchecked -->
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                      <rect x="3.5" y="3.5" width="17" height="17" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    </svg>
                  </span>
                </li>
              </ul>
            </div>

            <!-- Search input: limited max-width so it doesn't push other controls -->
            <div class="flex-1 min-w-[160px] max-w-[320px]">
              <div class="relative">
                <input
                  v-model="filterForm.q"
                  type="text"
                  placeholder="Course name or note"
                  class="w-full border rounded px-3 py-2 bg-white dark:bg-slate-800 text-sm pr-10 search-input"
                />
                <!-- search SVG instead of material icon -->
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="1.6" fill="none"/>
                </svg>
              </div>
            </div>

            <div class="flex gap-2">
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Apply</button>
              <button type="button" @click="resetFilters" class="px-3 py-2 border rounded">Reset</button>
            </div>
          </form>
        </div>

        <!-- Table (unchanged) -->
        <div class="bg-white shadow rounded-lg overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="s in schedules.data" :key="s.id">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ s.course?.name ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ s.day_of_week }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ s.start_time }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ s.end_time }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ s.note ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2 text-right">
                  <Link
                    v-if="canEdit"
                    :href="urlFor('admin.schedules.edit', s.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </Link>
                  <button
                    v-if="canDelete"
                    @click="confirmDelete(s.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>

              <tr v-if="!schedules.data || schedules.data.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  No schedules found.
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination styled similarly -->
          <div class="p-4 flex justify-end">
            <nav v-if="schedules.links" class="flex items-center gap-2">
              <template v-for="link in schedules.links" :key="link.label">
                <button
                  v-if="link.url"
                  v-html="link.label"
                  @click.prevent="goto(link.url)"
                  class="px-3 py-1 border rounded"
                  :class="{ 'bg-blue-600 text-white': link.active }"
                ></button>
                <span v-else class="px-2 text-gray-400" v-html="link.label"></span>
              </template>
            </nav>
          </div>
        </div>

        <!-- Confirm modal (unchanged) -->
        <div v-if="confirming" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
          <div class="bg-white p-6 rounded shadow max-w-sm w-full">
            <h3 class="font-semibold mb-2">Confirm delete</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete this schedule?</p>
            <div class="flex justify-end gap-2">
              <button @click="cancelDelete" class="px-4 py-2 border rounded">Cancel</button>
              <button @click="deleteNow" class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* small styles (kept the same as original student file's small styles) */
table { border-collapse: collapse; width: 100%; }
th { font-weight: 600; color: #1f2937; text-align: left; }
td { vertical-align: middle; }

/* dropdown and filter control */
.filter-control { display: inline-flex; align-items: center; gap: 0.5rem; }
.filter-control .truncate { max-width: 8rem; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* dropdown list styling */
ul { list-style: none; margin: 0; padding: 0; }
.filter-dropdown { box-shadow: 0 6px 18px rgba(2,6,23,0.08); }

/* icons sizes */
.filter-icon { width: 1.25rem; height: 1.25rem; display:inline-flex; align-items:center; justify-content:center; color: #64748b; }
.filter-icon-small { width: 1rem; height: 1rem; color: #94a3b8; }

/* ensure dropdown items readable */
.filter-dropdown li { font-size: 0.9375rem; }

/* improved button shapes for filter area */
button[ type="submit" ] { transition: background-color .15s ease; }
button[type="button"].filter-control { cursor: pointer; }

/* responsive tweaks */
@media (max-width: 640px) {
  .filter-control { min-width: 120px; }
  .search-input { max-width: 220px; }
}
</style>
