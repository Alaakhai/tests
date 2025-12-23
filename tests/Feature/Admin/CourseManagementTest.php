<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_courses_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Course::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get('/admin/courses');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_course()
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);

        $response = $this->actingAs($admin)->post('/admin/courses', [
            'name'       => 'Math',
            'code'       => 'MATH101',
            'teacher_id' => $teacher->id,
        ]);

        $response->assertRedirect('/admin/courses');

        $this->assertDatabaseHas('courses', [
            'name' => 'Math',
            'code' => 'MATH101',
        ]);
    }

    /** @test */
    public function admin_can_delete_course()
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create();

        $response = $this->actingAs($admin)
            ->delete("/admin/courses/{$course->id}");

        $response->assertRedirect('/admin/courses');

        $this->assertDatabaseMissing('courses', [
            'id' => $course->id,
        ]);
    }
}
