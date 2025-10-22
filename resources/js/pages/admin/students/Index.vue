<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// استلام بيانات الطلاب كـ props
defineProps({
  students: Array,
});

const searchQuery = ref('');
const encodeForm = useForm({});
const deleteForm = useForm({});

// دالة للحصول على رابط الصورة من المسار
const getPhotoUrl = (photoPath) => {
  return `/storage/${photoPath}`;
};

// تفعيل عملية الترميز
const triggerEncoding = () => {
  encodeForm.post(route('admin.students.encode'), {
    preserveState: true,
    onSuccess: () => {
      alert('Face Encoding Process Triggered Successfully!');
    },
    onError: () => {
      alert('An error occurred during encoding.');
    }
  });
};

// تفعيل زر تعديل الطالب
const editStudent = (studentId) => {
  window.location.href = route('admin.students.edit', studentId);
};

// تفعيل زر حذف الطالب مع تأكيد الحذف
const deleteStudent = (studentId) => {
  if (confirm("Are you sure you want to delete this student?")) {
    deleteForm.delete(route('admin.students.destroy', studentId), {
      preserveScroll: true,
      onSuccess: () => alert("Student deleted successfully."),
      onError: () => alert("Failed to delete student."),
    });
  }
};
</script>

<template>
  <Head title="Manage Students" />

  <AuthenticatedLayout>
    <div class="p-6 student-management-container min-h-screen">

      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <span class="text-2xl mr-3 icon-gradient">🎓</span>
          <h2 class="text-2xl font-semibold text-blue-900 leading-tight">Student Management</h2>
        </div>

        <Link 
          :href="route('admin.students.create')" 
          class="btn-add-student tech-button"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
            xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" 
            stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
          Add New Student
        </Link>
      </div>

      <div class="mb-6 p-4 bg-blue-50 rounded-lg shadow-sm border border-blue-200">
        <form @submit.prevent="triggerEncoding">
          <button 
            type="submit" 
            :disabled="encodeForm.processing" 
            class="w-full py-2.5 px-4 bg-blue-600 text-white font-semibold rounded-lg shadow-md border border-blue-500 tech-encode-btn"
          >
            Update Face Recognition Data
          </button>
        </form>
      </div>

      <div class="bg-white rounded-lg shadow-xl overflow-hidden border border-gray-200">

        <div class="p-4 border-b border-gray-200 bg-gray-50">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search students..."
            class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 py-2.5 px-4 bg-white text-gray-800 transition-all duration-300"
          >
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-blue-600">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Photo</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Courses Enrolled</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Actions</th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 text-gray-800">

              <tr v-for="student in students" :key="student.id" class="hover:bg-blue-50 transition-colors duration-200">

                <td class="px-6 py-4 whitespace-nowrap">
                  <img 
                    v-if="student.photos.length > 0" 
                    :src="getPhotoUrl(student.photos[0].photo_path)" 
                    alt="Student Photo" 
                    class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-300"
                  >
                  <div v-else class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500 border border-gray-300">
                    No Photo
                  </div>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ student.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ student.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-300">
                    {{ student.courses.length }} Courses
                  </span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                  <div class="flex space-x-2 justify-end">

                    <button @click="editStudent(student.id)" class="btn-edit-action tech-edit-btn">
                      <svg class="w-4 h-4 mr-1 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                        xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" 
                        stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l4 4m-4-4l4 4"></path></svg>
                      <span class="btn-text">Edit</span>
                      <div class="btn-glow"></div>
                    </button>

                    <button @click="deleteStudent(student.id)" class="btn-delete-action tech-delete-btn">
                      <svg class="w-4 h-4 mr-1 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                        xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" 
                        stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      <span class="btn-text">Delete</span>
                      <div class="btn-glow"></div>
                    </button>

                  </div>
                </td>
              </tr>

              <tr v-if="!students || students.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <span class="text-4xl mb-2 text-red-500">🚫</span>
                    <p class="text-lg">No students are currently registered in the system.</p>
                  </div>
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
/* -------------------------- */
/* Container Background Gradient */
/* -------------------------- */
.student-management-container {
  background: linear-gradient(135deg, #f0f9ff 0%, #e6f3ff 100%);
}

/* -------------------------- */
/* Icon Gradient for Title */
/* -------------------------- */
.icon-gradient {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.text-blue-900 {
  color: #1e3a8a;
}

/* -------------------------- */
/* Buttons Base Style */
/* -------------------------- */
.btn-add-student,
.tech-encode-btn,
.tech-edit-btn, 
.tech-delete-btn {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  transition: all 0.3s ease-in-out;
  color: white;
  overflow: hidden;
  z-index: 1;
  border-radius: 0.5rem;
}
.tech-edit-btn, 
.tech-delete-btn {
  font-size: 0.75rem;
  padding: 6px 12px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* -------------------------- */
/* Add New Student Button */
/* -------------------------- */
.btn-add-student {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
}
.btn-add-student:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.6);
}

/* -------------------------- */
/* Encoding Button */
/* -------------------------- */
.tech-encode-btn {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
  box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4);
}
.tech-encode-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.6);
}

/* -------------------------- */
/* Edit Button (Blue Gradient) */
/* -------------------------- */
.tech-edit-btn {
  background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
}
.tech-edit-btn:hover {
  transform: translateY(-1px);
  background: linear-gradient(135deg, #00c8ff 0%, #2a6bc9 100%);
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}

/* -------------------------- */
/* Delete Button (Red Gradient) */
/* -------------------------- */
.tech-delete-btn {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
}
.tech-delete-btn:hover {
  transform: translateY(-1px);
  background: linear-gradient(135deg, #ff5757 0%, #e04a42 100%);
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}

/* -------------------------- */
/* Glow Effect on Hover */
/* -------------------------- */
.btn-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
  z-index: 0;
}

.btn-edit-action:hover .btn-glow,
.btn-delete-action:hover .btn-glow,
.btn-add-student:hover::after,
.tech-encode-btn:hover::after {
  left: 100%;
}

.btn-add-student::after,
.tech-encode-btn::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
  z-index: 0;
}

/* -------------------------- */
/* Button Text and Icon Styling */
/* -------------------------- */
.btn-icon,
.btn-text {
  position: relative;
  z-index: 2;
  text-shadow: 0 1px 1px rgba(0,0,0,0.2);
}
.btn-icon {
  filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
}
</style>
