<?php

namespace Tests\Unit\Models;

use App\Models\Schedule;
use App\Models\Course;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function schedule_can_be_created()
    {
        $schedule = Schedule::factory()->create();

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
        ]);
    }

    /** @test */
    public function schedule_belongs_to_course()
    {
        $schedule = Schedule::factory()->create();

        $this->assertInstanceOf(Course::class, $schedule->course);
    }

    /** @test */
    public function schedule_belongs_to_teacher()
    {
        $schedule = Schedule::factory()->create();

        $this->assertInstanceOf(User::class, $schedule->teacher);
    }

    /** @test */
    public function schedule_belongs_to_classroom()
    {
        $schedule = Schedule::factory()->create();

        $this->assertInstanceOf(Classroom::class, $schedule->classroom);
    }

    /** @test */
    public function overlaps_with_returns_true_when_times_overlap()
    {
        $schedule = Schedule::factory()->create([
            'start_time' => '08:00',
            'end_time'   => '10:00',
        ]);

        $this->assertTrue(
            $schedule->overlapsWith('09:00', '11:00')
        );
    }

    /** @test */
    public function overlaps_with_returns_false_when_times_do_not_overlap()
    {
        $schedule = Schedule::factory()->create([
            'start_time' => '08:00',
            'end_time'   => '10:00',
        ]);

        $this->assertFalse(
            $schedule->overlapsWith('10:00', '12:00')
        );
    }

    /** @test */
    public function has_overlap_for_course_returns_true_when_conflict_exists()
    {
        $course = Course::factory()->create();

        Schedule::factory()->create([
            'course_id'  => $course->id,
            'day_of_week'=> 'Monday',
            'start_time' => '08:00',
            'end_time'   => '10:00',
        ]);

        $this->assertTrue(
            Schedule::hasOverlapForCourse(
                $course->id,
                'Monday',
                '09:00',
                '11:00'
            )
        );
    }

    /** @test */
    public function has_overlap_for_course_returns_false_when_no_conflict()
    {
        $course = Course::factory()->create();

        Schedule::factory()->create([
            'course_id'  => $course->id,
            'day_of_week'=> 'Monday',
            'start_time' => '08:00',
            'end_time'   => '10:00',
        ]);

        $this->assertFalse(
            Schedule::hasOverlapForCourse(
                $course->id,
                'Monday',
                '10:00',
                '12:00'
            )
        );
    }

    /** @test */
    public function has_overlap_for_teacher_returns_true_when_conflict_exists()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        Schedule::factory()->create([
            'teacher_id'=> $teacher->id,
            'day_of_week'=> 'Tuesday',
            'start_time'=> '09:00',
            'end_time'  => '11:00',
        ]);

        $this->assertTrue(
            Schedule::hasOverlapForTeacher(
                $teacher->id,
                'Tuesday',
                '10:00',
                '12:00'
            )
        );
    }

    /** @test */
    public function has_overlap_for_student_returns_true_when_conflict_exists()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course  = Course::factory()->create();

        // ربط الطالب بالمادة
        $course->students()->attach($student->id);

        Schedule::factory()->create([
            'course_id'  => $course->id,
            'day_of_week'=> 'Wednesday',
            'start_time'=> '08:00',
            'end_time'  => '10:00',
        ]);

        $this->assertTrue(
            Schedule::hasOverlapForStudent(
                $student->id,
                'Wednesday',
                '09:00',
                '11:00'
            )
        );
    }
}
