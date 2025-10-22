<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    photos: [], // Add photos array to the form
    // تضمين دور الطالب تلقائيًا
    role: 'student',
});

// To store image previews
const photoPreviews = ref([]);

const handlePhotoUpload = (event) => {
    // 🔑 يجب إضافة هذه الخاصية ليتمكن Inertia من إرسال الملفات بشكل صحيح
    form.photos = Array.from(event.target.files); 
    photoPreviews.value = []; // Clear previous previews

    // Generate new previews
    if (form.photos.length) {
        form.photos.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreviews.value.push(e.target.result);
            };
            reader.readAsDataURL(file);
        });
    }
};

const submit = () => {
    // يجب استخدام { forceFormData: true } مع Inertia لإرسال الملفات (الصور)
    form.post(route('admin.students.store'), {
        forceFormData: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Add New Student" />

    <AuthenticatedLayout>
        <template #header>
             <h2 class="font-bold text-2xl text-gray-800 leading-tight">إضافة طالب جديد</h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="p-10 bg-white rounded-3xl shadow-2xl border border-indigo-100 form-card-prominent">
                    
                    <div class="flex items-center mb-8 border-b pb-4 border-indigo-200">
                        <i class="fas fa-user-plus text-3xl text-indigo-700 mr-3"></i>
                        <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">بيانات الطالب والصور</h2>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label for="name" class="block mb-2 font-bold text-gray-800">اسم الطالب</label>
                            <input 
                                v-model="form.name" 
                                id="name" 
                                type="text" 
                                class="w-full input-field-prominent" 
                                required 
                            />
                            <div v-if="form.errors.name" class="text-red-600 text-sm mt-1 font-semibold">{{ form.errors.name }}</div>
                        </div>
                        
                        <div>
                            <label for="email" class="block mb-2 font-bold text-gray-800">البريد الإلكتروني</label>
                            <input 
                                v-model="form.email" 
                                id="email" 
                                type="email" 
                                class="w-full input-field-prominent" 
                                required 
                            />
                            <div v-if="form.errors.email" class="text-red-600 text-sm mt-1 font-semibold">{{ form.errors.email }}</div>
                        </div>
                        
                        <div>
                            <label for="password" class="block mb-2 font-bold text-gray-800">كلمة المرور</label>
                            <input 
                                v-model="form.password" 
                                id="password" 
                                type="password" 
                                class="w-full input-field-prominent" 
                                required 
                            />
                            <div v-if="form.errors.password" class="text-red-600 text-sm mt-1 font-semibold">{{ form.errors.password }}</div>
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block mb-2 font-bold text-gray-800">تأكيد كلمة المرور</label>
                            <input 
                                v-model="form.password_confirmation" 
                                id="password_confirmation" 
                                type="password" 
                                class="w-full input-field-prominent" 
                                required 
                            />
                        </div>

                        <div class="pt-4 border-t border-indigo-100">
                            <label for="photos" class="block mb-2 font-bold text-gray-800">صور الطالب (لمصادقة الوجه - اختر 3-5 صور)</label>
                            <input 
                                @change="handlePhotoUpload" 
                                id="photos" 
                                type="file" 
                                class="w-full file-input-prominent" 
                                multiple 
                                accept="image/*" 
                            />
                            <div v-if="form.errors.photos" class="text-red-600 text-sm mt-1 font-semibold">{{ form.errors.photos }}</div>
                        </div>

                        <div v-if="photoPreviews.length > 0" class="mt-4 grid grid-cols-5 gap-4">
                            <div v-for="(preview, index) in photoPreviews" :key="index" class="relative">
                                <img :src="preview" class="w-full aspect-square object-cover rounded-lg border-2 border-indigo-300 shadow-md" alt="Photo Preview" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="py-4 px-8 text-xl font-extrabold rounded-xl transition-all duration-300 transform hover:-translate-y-1 prominent-blue-button"
                            >
                                <span v-if="form.processing">جاري إنشاء الحساب...</span>
                                <span v-else>إنشاء حساب الطالب 🚀</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* -------------------------- */
/* Prominent Card & Field Styling (مكررة من النماذج السابقة) */
/* -------------------------- */

.form-card-prominent {
    box-shadow: 0 15px 35px rgba(49, 46, 129, 0.2), 0 5px 15px rgba(0, 0, 0, 0.05);
}

.input-field-prominent {
    @apply rounded-xl shadow-md px-4 py-3 transition-all duration-300 bg-white;
    border: 2px solid #D1D5DB; 
    font-size: 1rem;
}

.input-field-prominent:focus {
    @apply ring-0 border-transparent; 
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.4), inset 0 1px 3px rgba(0, 0, 0, 0.1); 
    border-color: #4F46E5;
}

/* -------------------------- */
/* File Input Styling (جديد للصور) */
/* -------------------------- */
.file-input-prominent {
    @apply block w-full text-sm text-gray-500
           file:mr-4 file:py-2 file:px-4
           file:rounded-full file:border-0
           file:text-sm file:font-semibold
           file:bg-indigo-50 file:text-indigo-700
           hover:file:bg-indigo-100;
    
    border: 1px solid #E0E7FF; /* Indigo-100 border for the main input */
    padding: 0.5rem;
    border-radius: 0.75rem;
    background-color: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}


/* -------------------------- */
/* Submit Button Styling - الزر الأزرق البارز */
/* -------------------------- */
.prominent-blue-button {
    background: linear-gradient(90deg, #4F46E5 0%, #3B82F6 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(79, 70, 229, 0.6); 
    border: none;
}

.prominent-blue-button:hover {
    background: linear-gradient(90deg, #3730A3 0%, #1D4ED8 100%);
    box-shadow: 0 10px 30px rgba(79, 70, 229, 0.8);
}
</style>