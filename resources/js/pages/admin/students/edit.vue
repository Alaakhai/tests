<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  student: { type: Object, required: true },
})

const student = props.student || {
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  photos: [],
}

const form = useForm({
  name: student.name,
  email: student.email,
  password: '',
  password_confirmation: '',
  photos: [],
})

const photoPreviews = ref([])

const handlePhotoUpload = (event) => {
  form.photos = Array.from(event.target.files)
  photoPreviews.value = []

  if (form.photos.length) {
    form.photos.forEach(file => {
      const reader = new FileReader()
      reader.onload = (e) => photoPreviews.value.push(e.target.result)
      reader.readAsDataURL(file)
    })
  }
}

const page = usePage()
const successMessage = computed(() => page.props.flash?.success || '')

const updateStudent = () => {
  form.put(route('admin.students.update', student.id), {
    onSuccess: () => {
      form.reset('password', 'password_confirmation', 'photos')
      photoPreviews.value = []
    },
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Edit Student Details" />

    <!-- Success Toast -->
    <div v-if="successMessage" class="w-full flex justify-center mt-6">
      <div
        class="w-full max-w-4xl bg-green-500 text-white px-8 py-4 rounded-3xl shadow-lg flex items-center justify-center"
      >
        <i class="fas fa-check-circle ml-3 text-xl"></i>
        <span class="text-lg font-bold">
          {{ successMessage }}
        </span>
      </div>
    </div>

    <!-- Card -->
    <div class="max-w-2xl mx-auto mt-10 p-10 bg-[#1e293b] rounded-3xl shadow-2xl border border-indigo-100 form-card-prominent">

      <!-- Header -->
      <div class="flex items-center mb-8 border-b pb-4 border-indigo-200">
        <i class="fas fa-user-edit text-3xl text-indigo-400 mr-3"></i>
        <h2 class="text-3xl font-extrabold text-white leading-tight">
          Edit Student Details
        </h2>
      </div>

      <form @submit.prevent="updateStudent" class="space-y-6">

        <!-- Name -->
        <div>
          <label for="name" class="block mb-2 font-bold text-gray-300">
            Student Name
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full input-field-prominent"
            required
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1 font-semibold">
            {{ form.errors.name }}
          </div>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block mb-2 font-bold text-gray-300">
            Email Address
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full input-field-prominent"
            required
          />
          <div v-if="form.errors.email" class="text-red-500 text-sm mt-1 font-semibold">
            {{ form.errors.email }}
          </div>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block mb-2 font-bold text-gray-300">
            Password (optional)
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="w-full input-field-prominent"
            placeholder="Leave blank if you do not want to change it"
          />
        </div>

        <!-- Password Confirmation -->
        <div>
          <label for="password_confirmation" class="block mb-2 font-bold text-gray-300">
            Confirm Password
          </label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="w-full input-field-prominent"
            placeholder="Re-enter the password"
          />
        </div>

        <!-- Photos -->
        <div>
          <label for="photos" class="block mb-2 font-bold text-gray-300">
            Update Student Photos (optional)
          </label>
          <input
            id="photos"
            type="file"
            multiple
            accept="image/*"
            @change="handlePhotoUpload"
            class="w-full input-field-prominent cursor-pointer"
          />
          <div v-if="form.errors.photos" class="text-red-500 text-sm mt-1 font-semibold">
            {{ form.errors.photos }}
          </div>
        </div>

        <!-- Photo Preview -->
        <div v-if="photoPreviews.length > 0" class="mt-6 grid grid-cols-3 gap-4">
          <div v-for="(preview, index) in photoPreviews" :key="index">
            <img
              :src="preview"
              class="h-24 w-24 object-cover rounded-xl border-2 border-indigo-200 shadow-md"
            />
          </div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-4 mt-6 text-xl font-extrabold rounded-xl transition-all duration-300 transform hover:-translate-y-1 prominent-submit-button"
        >
          <span v-if="form.processing">Updating...</span>
          <span v-else>💾 Save Changes</span>
        </button>

      </form>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.form-card-prominent {
  box-shadow:
    0 15px 35px rgba(49, 46, 129, 0.2),
    0 5px 15px rgba(0, 0, 0, 0.05);
}

.input-field-prominent {
  @apply rounded-xl shadow-md px-4 py-3 transition-all duration-300;
  background: #1e293b;
  color: #ffffff;
  border: 2px solid #d1d5db;
  font-size: 1rem;
}

.input-field-prominent::placeholder {
  color: #c7d2fe;
  opacity: 1;
}

.input-field-prominent:focus {
  @apply ring-0 border-transparent;
  box-shadow:
    0 0 0 4px rgba(99, 102, 241, 0.4),
    inset 0 1px 3px rgba(0, 0, 0, 0.1);
  border-color: #4f46e5;
}

.prominent-submit-button {
  background: linear-gradient(90deg, #4f46e5 0%, #3b82f6 100%);
  color: white;
  box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6);
  border: none;
}

.prominent-submit-button:hover {
  background: linear-gradient(90deg, #3730a3 0%, #1d4ed8 100%);
  box-shadow: 0 10px 30px rgba(79, 70, 229, 0.8);
}
</style>
