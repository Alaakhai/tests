<script setup>
import { computed } from 'vue';
import ApplicationLogo from '@/components/ApplicationLogo.vue';
import Dropdown from '@/components/Dropdown.vue';
import DropdownLink from '@/components/DropdownLink.vue';
import NavLink from '@/components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

// Access user role from shared props
const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

// Define navigation links for each role
const adminLinks = [
    { route: 'admin.dashboard', label: 'Dashboard', icon: 'fas fa-tachometer-alt' },
    { route: 'admin.users.index', label: 'User Management', icon: 'fas fa-users-cog' },
    { route: 'admin.courses.index', label: 'Course Management', icon: 'fas fa-book' },
    { route: 'admin.students.index', label: 'Student Management', icon: 'fas fa-user-graduate' },
    { route: 'admin.schedules.index', label: 'Schedule Management', icon: 'fas fa-calendar-alt' }, 
];

const teacherLinks = [
    { route: 'teacher.dashboard', label: 'المواد الدراسية', icon: 'fas fa-chalkboard' },
    { route: 'teacher.courses.create', label: 'اضافة مادة جديدة', icon: 'fas fa-folder-plus' },
    { route: 'teacher.students.create', label: 'إضافة طالب جديد', icon: 'fas fa-user-plus' }, 
];

const studentLinks = [
    { route: 'student.dashboard', label: 'Dashboard', icon: 'fas fa-home' },
    // { route: 'student.courses', label: 'My Courses' },
];

const getLinks = computed(() => {
    if (userRole.value === 'admin') return adminLinks;
    if (userRole.value === 'teacher') return teacherLinks;
    if (userRole.value === 'student') return studentLinks;
    return [];
});
</script>

<template>
    <div class="flex h-screen modern-layout">
        <!-- Sidebar with Modern Blue Gradient & Glassmorphism Effect -->
        <aside class="w-64 flex-shrink-0 sidebar-gradient text-white flex flex-col shadow-2xl sidebar-glass">
            
            <!-- Logo Section -->
            <div class="h-16 flex items-center justify-center border-b border-blue-700/50 logo-section">
                <Link :href="route('admin.dashboard')">
                    <ApplicationLogo class="block h-9 w-auto text-white/90" />
                </Link>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex-grow px-4 py-6 overflow-y-auto custom-scrollbar">
                <div class="space-y-2">
                    <NavLink 
                        v-for="link in getLinks" 
                        :key="link.route" 
                        :href="route(link.route)" 
                        :active="route().current(link.route)"
                        class="modern-nav-link"
                    >
                        <i :class="[link.icon, 'w-5 text-center mr-3']"></i>
                        {{ link.label }}
                    </NavLink>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden bg-gray-50">
            
            <!-- Header/Navbar -->
            <header class="bg-white shadow-lg flex justify-between items-center px-6 py-3 sticky top-0 z-10 header-shadow">
                <div class="font-semibold text-lg text-gray-800">
                    <slot name="header" />
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium leading-4 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                {{ $page.props.auth.user.name }}
                                <svg class="ms-2 -me-0.5 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')" class="text-gray-700 hover:bg-blue-50"> 
                                <i class="fas fa-user-circle mr-2"></i> Profile 
                            </DropdownLink>
                            <div class="border-t border-gray-100 my-1"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                <div class="container mx-auto px-6 py-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* -------------------------- */
/* Sidebar Base Styling (Applied to <aside>) */
/* -------------------------- */
.sidebar-gradient {
    /* Gradient: Dark blue to vibrant blue */
    background: linear-gradient(180deg, #1d4ed8 0%, #3b82f6 100%);
    position: relative;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}

/* -------------------------- */
/* Glassmorphism Effect for Sidebar */
/* -------------------------- */
.sidebar-glass {
    /* تطبيق الزجاجية: ضبابية طفيفة وشفافية في الخلفية */
    background: rgba(23, 3, 136, 0.9); /* أزرق داكن شبه شفاف */
    backdrop-filter: blur(8px); /* تأثير التضبيب */
    -webkit-backdrop-filter: blur(8px);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.logo-section {
    /* تأثير الضوء الخفيف على الخط الفاصل */
    box-shadow: 0 1px 0 rgba(61, 147, 187, 0.1) inset;
}

/* -------------------------- */
/* Navigation Link Styling (modern-nav-link) */
/* -------------------------- */
.modern-nav-link {
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.75rem; /* أكثر استدارة */
    color: #bfdbfe; /* blue-200 */
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); /* انتقال سلس */
    position: relative;
    overflow: hidden;
    z-index: 1;
}

/* Hover Effect: Light Glow */
.modern-nav-link:hover {
    color: #ffffff;
    background-color: rgba(13, 101, 160, 0.15); /* خلفية فاتحة شفافة عند التحويم */
    transform: translateX(5px); /* حركة بسيطة لليمين */
}

/* Active Link Styling: Modern Pulse/Blob Effect */
.modern-nav-link[data-active="true"] {
    color: #ffffff;
    font-weight: 700;
    background: linear-gradient(90deg, #2563eb 0%, #3b82f6 100%); /* تدرج أزرق حيوي */
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); /* ظل أزرق جذاب */
    transform: translateX(0);
}

/* Active Link: Adding a subtle border or line for depth */
.modern-nav-link[data-active="true"]::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 4px; /* خط جانبي سميك */
    background-color: #ffffff;
    border-radius: 0 4px 4px 0;
}

/* -------------------------- */
/* Scrollbar Styling for Sidebar (للعرض الأفضل) */
/* -------------------------- */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* -------------------------- */
/* Header Shadow Tweak */
/* -------------------------- */
.header-shadow {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); /* ظل أنعم وأحدث */
}
</style>
