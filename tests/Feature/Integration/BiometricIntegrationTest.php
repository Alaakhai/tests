<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Attendance;
use Carbon\Carbon;

class BiometricIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware; // تعطيل CSRF والميدلوير

    public function test_teacher_marks_attendance_when_flask_recognizes_face()
    {
        // 1) Fake Flask response
        Http::fake([
            'http://127.0.0.1:5000/recognize-face' => Http::response([
                'student_id' => 10
            ], 200),
        ]);

        // 2) Create teacher
        $teacher = User::factory()->create([
            'role' => 'teacher',
        ]);

        // 3) Create student (ID must match Flask response)
        $student = User::factory()->create([
            'id'   => 10,
            'role' => 'student',
        ]);

        // 4) Create course owned by teacher
        $course = Course::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        // 5) Create schedule linked to course
        $schedule = Schedule::factory()->create([
            'course_id' => $course->id,
        ]);

        // 6) Create initial attendance record (ABSENT)
        Attendance::create([
            'student_id'      => $student->id,
            'schedule_id'     => $schedule->id,
            'attendance_date' => Carbon::today(),
            'is_present'      => false,
        ]);

        // 7) Use real fixture image (NO GD)
        $image = new UploadedFile(
            base_path('tests/fixtures/test.png'),
            'test.png',
            'image/png',
            null,
            true
        );

        // 8) Call the real route
        $response = $this->actingAs($teacher)->post(
            '/teacher/attendance/mark',
            [
                'schedule_id' => $schedule->id,
                'image'       => $image,
            ]
        );

        // 9) Assertions: HTTP + JSON
        $response->assertStatus(200);
        $response->assertJson([
            'status'     => 'success',
            'student_id' => 10,
        ]);

        // 10) Assertions: Database updated
        $this->assertDatabaseHas('attendances', [
            'student_id'  => 10,
            'schedule_id' => $schedule->id,
            'is_present'  => true,
        ]);

        // 11) Assert external request was sent
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:5000/recognize-face'
                && $request->hasFile('image');
        });
    }
}
