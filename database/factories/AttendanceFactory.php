<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'schedule_id' => Schedule::factory(),

            // 🔴 هذا هو السطر الناقص
            'attendance_date' => now()->toDateString(),

            'attended_at' => now(),
            'departed_at' => null,
        ];
    }
}
