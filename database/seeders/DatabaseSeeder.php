<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use App\Models\Classroom;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1) Admin User (SAFE – no duplicates)
        |--------------------------------------------------------------------------
        */
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => UserRole::ADMIN,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 2) Teachers (5)
        |--------------------------------------------------------------------------
        */
        $teachers = User::factory()
            ->count(5)
            ->create([
                'role' => UserRole::TEACHER,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 3) Students (50)
        |--------------------------------------------------------------------------
        */
        $students = User::factory()
            ->count(50)
            ->create([
                'role' => UserRole::STUDENT,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 4) Classrooms
        |--------------------------------------------------------------------------
        */
        $this->call(ClassroomSeeder::class);

        /*
        |--------------------------------------------------------------------------
        | 5) Courses (10) + assign teacher_id
        |--------------------------------------------------------------------------
        */
        $courses = Course::factory()
            ->count(10)
            ->create();

        $courses->each(function (Course $course) use ($teachers) {
            if (empty($course->teacher_id)) {
                $course->update([
                    'teacher_id' => $teachers->random()->id,
                ]);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 6) Enroll students in 3 random courses (pivot: course_student)
        |--------------------------------------------------------------------------
        */
        $students->each(function (User $student) use ($courses) {
            $student->courses()->syncWithoutDetaching(
                $courses->random(3)->pluck('id')->toArray()
            );
        });
    }
}
