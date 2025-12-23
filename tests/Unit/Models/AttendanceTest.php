<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function attendance_can_be_created()
    {
        $student  = User::factory()->create();
        $schedule = Schedule::factory()->create();

        Attendance::create([
            'student_id'      => $student->id,
            'schedule_id'     => $schedule->id,
            'attendance_date' => now()->toDateString(),
            'attended_at'     => now(),
            'is_present'      => true,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id'  => $student->id,
            'schedule_id' => $schedule->id,
            'is_present'  => true,
        ]);
    }

    #[Test]
    public function attendance_belongs_to_student()
    {
        $attendance = Attendance::factory()->create();

        $this->assertInstanceOf(User::class, $attendance->student);
    }

    #[Test]
    public function attendance_belongs_to_schedule()
    {
        $attendance = Attendance::factory()->create();

        $this->assertInstanceOf(Schedule::class, $attendance->schedule);
    }

    #[Test]
    public function attendance_dates_are_casted_to_carbon()
    {
        $attendance = Attendance::factory()->create([
            'attendance_date' => '2025-01-01',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $attendance->attendance_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $attendance->attended_at);
    }
}
