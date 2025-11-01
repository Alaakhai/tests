<script setup>
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    course: Object,
    schedule: Object,
    todaysAttendance: Array,
});

const video = ref(null);
const canvas = ref(null);
let stream = null;
let captureInterval = null;

// اجعل قائمة الحضور تفاعلية لتتحدث الواجهة
const attendanceList = reactive(
    props.todaysAttendance.map(att => ({ ...att }))
);

// قفل لمنع إرسال طلبات متزامنة كثيرة
const isRecognizing = ref(false);

const startCamera = async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
        if (video.value) {
            video.value.srcObject = stream;
        }
    } catch (err) {
        console.error("Error accessing camera: ", err);
        alert("Could not access the camera. Please check permissions.");
    }
};

const stopCamera = () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    if (captureInterval) {
        clearInterval(captureInterval);
    }
};

const captureAndSendFrame = async () => {
    if (!video.value || !canvas.value || isRecognizing.value) return;

    try {
        isRecognizing.value = true;

        const context = canvas.value.getContext('2d');
        canvas.value.width = video.value.videoWidth || 640;
        canvas.value.height = video.value.videoHeight || 480;
        context.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);

        const blob = await new Promise(resolve => canvas.value.toBlob(resolve, 'image/jpeg', 0.9));

        const formData = new FormData();
        // ملاحظة مهمة: Laravel غالبًا يتوقع الحقل باسم "photo"
        formData.append('photo', blob, 'frame.jpg');
        formData.append('schedule_id', props.schedule.id);

        const response = await axios.post(route('teacher.attendance.mark'), formData);

        // نقرأ النتيجة بطريقتين: إن أعاد Laravel مباشرة student_id
        // أو إن مرّر استجابة Flask كما هي داخل recognized[0].student_id
        const directId = response?.data?.student_id ?? null;
        const nestedId = response?.data?.recognized?.[0]?.student_id ?? null;

        const studentId = directId ?? nestedId;

        if (studentId != null) {
            const sid = Number(studentId);
            const studentToUpdate = attendanceList.find(att => Number(att.student_id) === sid);
            if (studentToUpdate && !studentToUpdate.is_present) {
                studentToUpdate.is_present = true;
            }
        } else {
            // لم يتم التعرف - بإمكانك إظهار شيء بسيط أو تجاهله
            // console.debug('Unknown face or no match this frame');
        }
    } catch (error) {
        // راقب الرد إن أردت: console.error(error?.response?.data || error.message);
    } finally {
        isRecognizing.value = false;
    }
};

// --- إنهاء الجلسة ---
const endSession = () => {
    if (confirm('Are you sure you want to end the session? This will send notifications to absent students.')) {
        stopCamera();
        router.post(route('teacher.attendance.end', { course: props.course.id }), {
            schedule_id: props.schedule.id,
        });
    }
};

onMounted(() => {
    startCamera();
    // التقط إطار كل 3 ثوانٍ
    captureInterval = setInterval(captureAndSendFrame, 3000);
});

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <Head title="Attendance Session" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Live Attendance: {{ course.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-4 flex justify-end">
                    <form @submit.prevent="endSession">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-75">
                            End Session & Notify Absentees
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 p-4 bg-black rounded-lg shadow">
                        <video ref="video" autoplay playsinline class="w-full h-auto rounded-md"></video>
                        <canvas ref="canvas" class="hidden"></canvas>
                    </div>

                    <div class="md:col-span-1 p-4 bg-white rounded-lg shadow">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance Status</h3>
                        <ul class="space-y-3">
                            <li v-for="att in attendanceList" :key="att.id" class="flex items-center justify-between">
                                <span class="text-gray-800">{{ att.student.name }}</span>
                                <span v-if="att.is_present" class="px-3 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-800">
                                    Present
                                </span>
                                <span v-else class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800">
                                    Absent
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
