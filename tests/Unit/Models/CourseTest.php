<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function course_can_be_created()
    {
        $course = Course::factory()->create();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => $course->name,
        ]);
    }

    /** @test */
    public function course_has_many_schedules()
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Collection::class,
            $course->schedules
        );
    }
}
