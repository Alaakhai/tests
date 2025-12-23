<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\StudentPhoto;
use App\Models\User;

class StudentPhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_photo_can_be_created()
    {
        $student = User::create([
            'name' => 'Student One',
            'email' => 'student1@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $photo = StudentPhoto::create([
            'student_id' => $student->id,
            'photo_path' => 'students/photos/photo1.jpg',
        ]);

        $this->assertDatabaseHas('student_photos', [
            'student_id' => $student->id,
            'photo_path' => 'students/photos/photo1.jpg',
        ]);
    }

    public function test_student_photo_belongs_to_student()
    {
        $student = User::create([
            'name' => 'Student Two',
            'email' => 'student2@test.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $photo = StudentPhoto::create([
            'student_id' => $student->id,
            'photo_path' => 'students/photos/photo2.jpg',
        ]);

        $this->assertEquals($student->id, $photo->student_id);
    }
}
