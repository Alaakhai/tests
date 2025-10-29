<script setup>
/*
  resources/js/Pages/admin/Schedules/Create.vue
  Simple Create form for schedule using Inertia useForm
  - Uses AuthenticatedLayout
  - Posts to /admin/schedules (or route('admin.schedules.store') if Ziggy enabled)
  - Normalizes time inputs to HH:MM and checks end > start on client-side
*/

import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'

const form = useForm({
  course_id: '',
  day_of_week: '',
  start_time: '',
  end_time: '',
  note: '',
})

// simple local error for time ordering
const localTimeError = ref('')

// small helper to build URL if Ziggy not available
function urlFor(name, params = null) {
  try {
    // eslint-disable-next-line no-undef
    return typeof route === 'function' ? route(name, params) : '/admin/schedules'
  } catch (e) {
    return '/admin/schedules'
  }
}

const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']

/** normalizeTime
 * Ensure time string is HH:MM (slice first 5 chars). Accepts HH:MM or HH:MM:SS.
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

  // prefer Ziggy route if available, fallback to literal URL
  const target = urlFor('admin.schedules.store')
  // useForm.post handles errors and processing state
  form.post(target, {
    onSuccess: () => {
      // optional: redirect handled by server
    },
    onError: (errs) => {
      console.log('validation errors', errs)
    }
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Create Schedule" />

    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Create Schedule</h2>
        <Link href="/admin/schedules" class="text-sm text-blue-600">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4 max-w-md">
        <div>
          <label class="block text-sm mb-1">Course ID</label>
          <input v-model="form.course_id" type="text" class="border px-3 py-2 w-full" />
          <div v-if="form.errors.course_id" class="text-red-600 text-sm mt-1">{{ form.errors.course_id }}</div>
        </div>

        <div>
          <label class="block text-sm mb-1">Day</label>
          <select v-model="form.day_of_week" class="border px-3 py-2 w-full">
            <option value="">Select day</option>
            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
          </select>
          <div v-if="form.errors.day_of_week" class="text-red-600 text-sm mt-1">{{ form.errors.day_of_week }}</div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Start time</label>
            <input v-model="form.start_time" type="time" class="border px-3 py-2 w-full" />
            <div v-if="form.errors.start_time" class="text-red-600 text-sm mt-1">{{ form.errors.start_time }}</div>
          </div>
          <div>
            <label class="block text-sm mb-1">End time</label>
            <input v-model="form.end_time" type="time" class="border px-3 py-2 w-full" />
            <div v-if="form.errors.end_time" class="text-red-600 text-sm mt-1">{{ form.errors.end_time }}</div>
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
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded">
            Create
          </button>
          <Link href="/admin/schedules" class="px-4 py-2 border rounded">Cancel</Link>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* بسيط لتحسين شكل الحقول */
input, select { border-radius: 6px; }
</style>
