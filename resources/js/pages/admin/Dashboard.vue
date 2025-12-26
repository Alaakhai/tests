<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  recentCheckIns: Array,
  notifications: Array,
  chartData: Object,
})

const chartRef = ref(null)

onMounted(() => {
  if (chartRef.value) {
    new Chart(chartRef.value, {
      type: 'line',
      data: props.chartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#9ca3af' }
          },
          y: {
            grid: {
              borderDash: [5, 5],
              color: 'rgba(59,130,246,0.25)'
            },
            ticks: {
              callback: v => v + '%',
              color: '#9ca3af'
            }
          }
        },
        elements: {
          line: {
            tension: 0.45,
            borderColor: '#3b82f6',
            borderWidth: 3
          },
          point: {
            radius: 4,
            backgroundColor: '#60a5fa',
            hoverRadius: 6
          }
        }
      }
    })
  }
})
</script>

<template>
<Head title="Dashboard" />

<AuthenticatedLayout>
  <div class="dashboard-container min-h-screen p-4 sm:p-6 space-y-8">

    <!-- ===================== Page Title ===================== -->
    <h1 class="dashboard-title text-gradient">
      Dashboard
    </h1>

    <!-- ===================== Stats ===================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
      <div class="stat-card">
        <div>
          <p class="text-sm text-blue-300/70">Total Students</p>
          <p class="stat-number">{{ stats.totalStudents }}</p>
        </div>
        <i class="fas fa-users card-icon"></i>
      </div>

      <div class="stat-card">
        <div>
          <p class="text-sm text-blue-300/70">Total Teachers</p>
          <p class="stat-number">{{ stats.totalTeachers }}</p>
        </div>
        <i class="fas fa-chalkboard-teacher card-icon"></i>
      </div>

      <div class="stat-card">
        <div>
          <p class="text-sm text-blue-300/70">Total Courses</p>
          <p class="stat-number">{{ stats.totalCourses }}</p>
        </div>
        <i class="fas fa-book-open card-icon"></i>
      </div>

      <div class="stat-card">
        <div>
          <p class="text-sm text-blue-300/70">Daily Attendance</p>
          <p class="stat-number">
            {{ chartData.datasets[0].data.at(-1) }}%
          </p>
        </div>
        <i class="fas fa-check-circle card-icon"></i>
      </div>
    </div>

    <!-- ===================== Chart ===================== -->
    <div class="chart-card">
      <div class="flex justify-between items-center mb-4">
        <h2 class="section-title">Weekly Attendance Trend</h2>
        <span class="trend-badge">
          {{ chartData.datasets[0].data.at(-1) }}%
        </span>
      </div>

      <div class="chart-wrapper">
        <canvas ref="chartRef"></canvas>
      </div>
    </div>

    <!-- ===================== Lists ===================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="list-card">
        <h2 class="section-title">Recent Check-Ins</h2>
        <ul>
          <li
            v-for="(item, index) in recentCheckIns"
            :key="index"
            class="list-item"
          >
            <span class="flex items-center">
              <i class="fas fa-user-check text-blue-400 mr-2"></i>
              {{ item.name }}
            </span>
            <span class="time-badge">{{ item.time }}</span>
          </li>
        </ul>
      </div>

      <div class="list-card">
        <h2 class="section-title">Notifications</h2>
        <ul>
          <li
            v-for="(notification, index) in notifications"
            :key="index"
            :class="[
              'notification-item',
              notification.type === 'alert' ? 'alert' : 'info'
            ]"
          >
            <i :class="['fas', notification.icon, 'mr-3 mt-1']"></i>
            <p>{{ notification.message }}</p>
          </li>
        </ul>
      </div>
    </div>

  </div>
</AuthenticatedLayout>
</template>

<style scoped>
/* ===================== Background ===================== */
.dashboard-container{
  background: radial-gradient(circle at top, #0f172a, #020617);
}

/* ===================== Title ===================== */
.text-gradient{
  background: linear-gradient(to right, #4F46E5, #3B82F6);
  -webkit-background-clip: text;
  color: transparent;
}

.dashboard-title{
  font-size: 1.875rem;   /* أصغر شوي */
  font-weight: 800;
  letter-spacing: 1.5px;
  line-height: 1.2;
}

/* ===================== Cards ===================== */
.stat-card,
.chart-card,
.list-card{
  background: linear-gradient(180deg, #020617, #020617);
  border-radius: 16px;
  padding: 1.1rem;
  position: relative;
  overflow: hidden;
  box-shadow:
    inset 0 0 30px rgba(59,130,246,0.25),
    0 15px 40px rgba(0,0,0,0.7);
}

.stat-number{
  font-size: 1.6rem;
  font-weight: 700;
  color: #e5f0ff;
}

.card-icon{
  font-size: 2rem;
  color: #60a5fa;
  filter: drop-shadow(0 0 10px rgba(96,165,250,0.7));
}

/* ===================== Chart ===================== */
.chart-wrapper{
  position: relative;
  width: 100%;
  height: 300px;
}

.trend-badge{
  background: rgba(16,185,129,0.15);
  color: #10b981;
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
  font-weight: 600;
}

/* ===================== Lists ===================== */
.section-title{
  color: #bfdbfe;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.list-item{
  display: flex;
  justify-content: space-between;
  padding: 0.75rem;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  color: #e5f0ff;
}

.time-badge{
  background: rgba(59,130,246,0.2);
  color: #93c5fd;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
}

.notification-item{
  display: flex;
  padding: 0.75rem;
  border-radius: 10px;
  margin-bottom: 0.5rem;
  color: #e5f0ff;
}

.notification-item.info{
  background: rgba(59,130,246,0.15);
}

.notification-item.alert{
  background: rgba(239,68,68,0.15);
  color: #fecaca;
}

/* ===================== Glow ===================== */
.stat-card::before,
.chart-card::before,
.list-card::before{
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 18px;
  background: linear-gradient(
    to bottom,
    rgba(96,165,250,0.55),
    rgba(96,165,250,0.25),
    rgba(96,165,250,0.08),
    transparent
  );
  filter: blur(12px);
}

.stat-card::after,
.chart-card::after,
.list-card::after{
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(to right, transparent, #60a5fa, transparent);
}

@media (max-width: 768px){
  .chart-wrapper{ height: 220px; }
}
@media (max-width: 480px){
  .chart-wrapper{ height: 200px; }
}
</style>
