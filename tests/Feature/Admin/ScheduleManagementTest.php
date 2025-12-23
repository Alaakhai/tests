<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Classroom;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_schedules_list()
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($admin)
            ->get('/admin/schedules')
            ->assertInertia(fn ($page) =>
                $page->component('admin/Schedules/Index')
            );
    }

    /** @test */
    public function admin_can_create_schedule()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $classroom = Classroom::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/schedules', [
            'course_id'    => $course->id,
            'teacher_id'   => $teacher->id,
            'classroom_id' => $classroom->id,
            'day_of_week'  => 'Monday',
            'start_time'   => '09:00',
            'end_time'     => '11:00',
            'notes'        => 'Test',
            'is_active'    => true,
        ]);

        $response->assertRedirect('/admin/schedules');

        $this->assertDatabaseHas('schedules', [
            'course_id'   => $course->id,
            'day_of_week' => 'Monday',
        ]);
    }

    /** @test */
    public function admin_can_update_schedule()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $teacher = User::factory()->create(['role' => UserRole::TEACHER]);
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $classroom = Classroom::factory()->create();

        $schedule = Schedule::factory()->create([
            'course_id'    => $course->id,
            'teacher_id'   => $teacher->id,
            'classroom_id' => $classroom->id,
            'day_of_week'  => 'Sunday',
            'start_time'   => '08:00',
            'end_time'     => '10:00',
        ]);

        $this->actingAs($admin)->put(
            "/admin/schedules/{$schedule->id}",
            [
                'course_id'    => $course->id,
                'teacher_id'   => $teacher->id,
                'classroom_id' => $classroom->id,
                'day_of_week'  => 'Tuesday',
                'start_time'   => '10:00',
                'end_time'     => '12:00',
            ]
        )->assertRedirect('/admin/schedules');

        $this->assertDatabaseHas('schedules', [
            'id'          => $schedule->id,
            'day_of_week' => 'Tuesday',
        ]);
    }

    /** @test */
    public function admin_can_delete_schedule()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $schedule = Schedule::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/schedules/{$schedule->id}")
            ->assertRedirect('/admin/schedules');

        $this->assertDatabaseMissing('schedules', [
            'id' => $schedule->id,
        ]);
    }
}
