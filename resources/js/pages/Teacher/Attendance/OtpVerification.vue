<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

defineProps({
  courseId: Number,
  unverifiedAttendances: Array
})

const page = usePage()

const successMessage = computed(() => page.props.flash?.success)
const errorMessage = computed(() => page.props.errors?.otp)

const form = useForm({
  attendance_id: null,
  otp: ''
})

const submit = (attendanceId) => {
  form.attendance_id = attendanceId
  form.post(route('teacher.attendance.otp.verify'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('otp')
    }
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      OTP Verification
    </template>

    <!-- رسائل -->
    <div v-if="successMessage" class="mb-4 rounded bg-green-100 p-4 text-green-700">
      {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="mb-4 rounded bg-red-100 p-4 text-red-700">
      {{ errorMessage }}
    </div>

    <!-- القائمة -->
    <div v-if="unverifiedAttendances.length" class="space-y-4">
      <div
        v-for="att in unverifiedAttendances"
        :key="att.id"
        class="flex items-center justify-between rounded bg-white p-4 shadow"
      >
        <div>
          <p class="font-semibold">{{ att.student.name }}</p>
          <p class="text-sm text-gray-500">غير مؤكد الحضور</p>
        </div>

        <div class="flex items-center gap-2">
          <input
            v-model="form.otp"
            maxlength="6"
            placeholder="OTP"
            class="w-28 rounded border px-3 py-2 text-center focus:ring focus:ring-indigo-300"
          />
          <button
            @click="submit(att.id)"
            class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
          >
            تأكيد
          </button>
        </div>
      </div>
    </div>

    <div v-else class="rounded bg-gray-100 p-6 text-center text-gray-600">
      لا يوجد طلاب بحاجة إلى تحقق OTP 🎉
    </div>
  </AuthenticatedLayout>
</template>
