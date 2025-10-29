<script setup>
/*
  resources/js/Pages/admin/Schedules/Edit.vue
  Edit schedule form using Inertia useForm
  - Uses AuthenticatedLayout
  - Receives props: schedule (object), courses (optional array)
  - Normalizes time inputs to HH:MM before submitting
*/

import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'

// Props from Laravel controller
const props = defineProps({
  schedule: { type: Object, required: true },
  courses: { type: Array, default: () => [] },
})

// Days list for select
const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

// Initialize the form with schedule data
const form = useForm({
  course_id: props.schedule.course_id ?? '',
  day_of_week: props.schedule.day_of_week ?? '',
  start_time: props.schedule.start_time ?? '',
  end_time: props.schedule.end_time ?? '',
  note: props.schedule.note ?? '',
})

// Simple client-side error for time ordering (not replacing server validation)
const localTimeError = ref('')

// Helper: build URL with fallback if Ziggy is missing
function urlFor(name, params = null) {
  try {
    // eslint-disable-next-line no-undef
    return typeof route === 'function' ? route(name, params) : fallbackFor(name, params)
  } catch (e) {
    return fallbackFor(name, params)
  }
}

function fallbackFor(name, params = null) {
  switch (name) {
    case 'admin.schedules.update':
      return `/admin/schedules/${typeof params === 'object' ? params.id ?? '' : params}`
    case 'admin.schedules.index':
      return '/admin/schedules'
    default:
      return '/admin/schedules'
  }
}

/** normalizeTime
 * Ensure time string is HH:MM (slice first 5 chars). Accepts formats like HH:MM or HH:MM:SS.
 */
function normalizeTime(value) {
  if (!value) return ''
  const s = String(value).trim()
  return s.length >= 5 ? s.slice(0, 5) : s
}

/** compareTimes: returns true if end > start (both 'HH:MM') */
function compareTimes(start, end) {
  if (!start || !end) return true // let server handle required checks
  const toMinutes = t => {
    const [h, m] = String(t).split(':').map(v => parseInt(v, 10) || 0)
    return h * 60 + m
  }
  return toMinutes(end) > toMinutes(start)
}

// Submit update request (normalizes times and checks ordering)
function submit() {
  // normalize times to HH:MM (in case browser sends seconds)
  form.start_time = normalizeTime(form.start_time)
  form.end_time = normalizeTime(form.end_time)

  // client-side check: end must be after start
  if (!compareTimes(form.start_time, form.end_time)) {
    localTimeError.value = 'End time must be later than start time.'
    return
  }
  localTimeError.value = ''

  const target = urlFor('admin.schedules.update', props.schedule.id)
  form.put(target, {
    onSuccess: () => {
      // optional: could show toast; server usually redirects with flash
      console.log('Schedule updated successfully')
    },
    onError: (errs) => {
      console.error('Validation errors:', errs)
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Edit Schedule" />

    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Edit Schedule</h2>
        <Link :href="urlFor('admin.schedules.index')" class="text-sm text-blue-600">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4 max-w-md">
        <div>
          <label class="block text-sm mb-1">Course</label>
          <select v-model="form.course_id" class="border px-3 py-2 w-full">
            <option value="">Select Course</option>
            <option v-for="c in courses" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
          <div v-if="form.errors.course_id" class="text-red-600 text-sm mt-1">
            {{ form.errors.course_id }}
          </div>
        </div>

        <div>
          <label class="block text-sm mb-1">Day</label>
          <select v-model="form.day_of_week" class="border px-3 py-2 w-full">
            <option value="">Select day</option>
            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
          </select>
          <div v-if="form.errors.day_of_week" class="text-red-600 text-sm mt-1">
            {{ form.errors.day_of_week }}
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Start time</label>
            <input v-model="form.start_time" type="time" class="border px-3 py-2 w-full" />
            <div v-if="form.errors.start_time" class="text-red-600 text-sm mt-1">
              {{ form.errors.start_time }}
            </div>
          </div>
          <div>
            <label class="block text-sm mb-1">End time</label>
            <input v-model="form.end_time" type="time" class="border px-3 py-2 w-full" />
            <div v-if="form.errors.end_time" class="text-red-600 text-sm mt-1">
              {{ form.errors.end_time }}
            </div>
          </div>
        </div>

        <div v-if="localTimeError" class="text-red-600 text-sm">
          {{ localTimeError }}
        </div>

        <div>
          <label class="block text-sm mb-1">Note</label>
          <input v-model="form.note" type="text" class="border px-3 py-2 w-full" />
        </div>

        <div class="flex gap-2">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-green-600 text-white rounded"
          >
            Update
          </button>
          <Link :href="urlFor('admin.schedules.index')" class="px-4 py-2 border rounded">
            Cancel
          </Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* تحسين شكل الحقول */
input, select { border-radius: 6px; }
</style>
