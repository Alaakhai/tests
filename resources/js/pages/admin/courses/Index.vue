<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; 
import { ref } from 'vue';

defineProps({
    // نفترض أن هذه البيانات قادمة من Laravel/Controller
    courses: Array,
});

const searchQuery = ref(''); 
</script>

<template>
    <Head title="Course Management" />
    <AuthenticatedLayout>
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-4">
            <div class="flex justify-between items-center mb-6">
                
                <div class="flex items-center">
                    <span class="text-2xl mr-3 text-blue-500 icon-gradient">📚</span> 
                    <h2 class="text-2xl font-semibold text-blue-900 leading-tight">Course Management</h2>
                </div>
                
                <Link 
                    :href="'#'" 
                    class="bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-bold py-2.5 px-5 rounded-xl flex items-center shadow-lg transition-all duration-300 transform hover:-translate-y-1 tech-button"
                >
                    <span class="text-xl mr-2 font-bold">+</span> 
                    Add Course
                </Link>
            </div>
        </div>

        <div class="pb-4 pt-0 user-management-container"> 
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white shadow-xl rounded-xl overflow-hidden border border-blue-100">
                    
                    <div class="p-5 border-b border-blue-100 bg-blue-50">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Search courses..."
                            class="w-full border border-blue-200 rounded-xl shadow-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-200 py-3 px-4 bg-white transition-all duration-300"
                        >
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-200">
                            <thead class="bg-blue-600">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-blue-100 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-blue-100 uppercase tracking-wider">Code</th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-blue-100 uppercase tracking-wider">Teacher</th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-blue-100 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            
                            <tbody class="bg-white divide-y divide-blue-100">
                                <tr
                                    v-for="course in courses"
                                    :key="course.id"
                                    class="hover:bg-blue-50 transition-colors duration-200"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-900">{{ course.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700">{{ course.code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700">{{ course.teacher?.name ?? 'N/A' }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex space-x-2 justify-center">
                                            
                                            <Link :href="'#'" class="edit-tech-btn">
                                                <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-7-7l4 4m-4-4l4 4"></path></svg>
                                                <span class="btn-text">Edit</span>
                                                <div class="btn-glow"></div>
                                            </Link>
                                            
                                            <button class="delete-tech-btn">
                                                <svg class="w-4 h-4 btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span class="btn-text">Delete</span>
                                                <div class="btn-glow"></div>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="!courses || courses.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-blue-400">
                                        <div class="flex flex-col items-center">
                                            <span class="text-4xl mb-2">🚫</span> <p class="text-lg">No courses found.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </AuthenticatedLayout>
</template>

<style scoped>
/* -------------------------- */
/* Global Container Styling (الخلفية المطلوبة) */
/* -------------------------- */
.user-management-container {
    /* تطبيق الخلفية الزرقاء الهادئة: تدرج من أزرق فاتح جدًا إلى أزرق أفتح */
    background: linear-gradient(135deg, #f0f9ff 0%, #e6f3ff 100%);
    min-height: 100vh;
}

/* -------------------------- */
/* Typography and Icons */
/* -------------------------- */
.icon-gradient {
    /* Text fill with a blue gradient */
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.text-blue-900 {
    color: #1e3a8a; /* لضمان لون أزرق داكن متناسق مع الخلفية */
}

/* -------------------------- */
/* Action Buttons Styling */
/* -------------------------- */

/* Base style for Edit/Delete Buttons */
.edit-tech-btn, 
.delete-tech-btn {
    position: relative;
    border: none;
    padding: 8px 16px; 
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 4px; 
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    z-index: 1; 
}

/* Edit Button Colors */
.edit-tech-btn {
    background: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%); /* Cyan to Blue */
    color: white;
}

/* Delete Button Colors */
.delete-tech-btn {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); /* Bright Red to Deep Red */
    color: white;
}

/* Hover Effects (Lift and Shadow) */
.edit-tech-btn:hover, 
.delete-tech-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
}

.edit-tech-btn:hover {
    background: linear-gradient(135deg, #00c8ff 0%, #2a6bc9 100%);
}

.delete-tech-btn:hover {
    background: linear-gradient(135deg, #ff5757 0%, #e04a42 100%);
}

/* Glow Effect for Edit/Delete Buttons */
.btn-glow {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.4), 
        transparent);
    transition: left 0.5s ease;
    z-index: 0; 
}

.edit-tech-btn:hover .btn-glow,
.delete-tech-btn:hover .btn-glow {
    left: 100%;
}

.btn-icon,
.btn-text {
    position: relative;
    z-index: 2; 
}

/* -------------------------- */
/* Add Course Button (Header) */
/* -------------------------- */
.tech-button {
    position: relative;
    overflow: hidden;
}

/* Glow Effect for Add Course Button */
.tech-button::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, 
        transparent, 
        rgba(255, 255, 255, 0.2), 
        transparent);
    transition: left 0.5s ease;
}

.tech-button:hover::after {
    left: 100%;
}

/* -------------------------- */
/* Text/Icon Shadow Enhancements */
/* -------------------------- */
.btn-icon {
    font-size: 0.9rem;
    filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));
}

.btn-text {
    text-shadow: 0 1px 1px rgba(0,0,0,0.2);
}

/* -------------------------- */
/* Shadow and Corner Enhancements */
/* -------------------------- */
.shadow-lg {
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.15), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
}

.rounded-xl {
    border-radius: 0.75rem;
}

/* -------------------------- */
/* General Transition/Interaction */
/* -------------------------- */
.transition-all {
    transition: all 0.3s ease;
}

/* -------------------------- */
/* Tailwind Overrides for Coherence */
/* -------------------------- */
.bg-blue-600 { background-color: #2563eb; }
.hover\:bg-blue-50:hover { background-color: #eff6ff; }
.focus\:border-blue-400:focus { border-color: #60a5fa; }
.focus\:ring-blue-200:focus { --tw-ring-color: #bfdbfe; }
.bg-blue-50 { background-color: #eff6ff; }
.border-blue-100 { border-color: #dbeafe; }
.text-blue-700 { color: #1d4ed8; }
.text-blue-900 { color: #1e3a8a; }
.text-blue-400 { color: #60a5fa; }
</style>