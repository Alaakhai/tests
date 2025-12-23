<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'day_of_week' => $this->faker->randomElement([
                'Saturday',
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
            ]),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'teacher_id' => User::factory(),
            'classroom_id' => Classroom::factory(),
            'is_active' => true,
        ];
    }
}
